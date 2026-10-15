-- =====================================================================
-- Purga de ventas antiguas CON RESPALDO COMPRIMIDO - multisalas
--
-- Que hace:
--   1. Copia las ventas viejas (sales, details, tickets), el enlace de las
--      anulaciones y los audits a la base "multisalas_historico", en tablas
--      comprimidas (motor ARCHIVE; si el servidor no lo tiene, InnoDB COMPRESSED).
--   2. VERIFICA que cada fila a borrar ya este copiada en el historico.
--   3. Solo si todo cuadra, borra de la base principal. Si algo no cuadra,
--      NO BORRA NADA y lo dice al final.
--
-- Se puede ejecutar de nuevo cada cierto tiempo cambiando @fecha_corte:
-- agrega lo nuevo al historico sin duplicar.
--
-- Como ejecutar:
--   - Antes: respaldo completo con mysqldump.
--   - En horario sin ventas (los DELETE y OPTIMIZE bloquean tablas).
--   - Ejecutar como SCRIPT completo (DBeaver: Alt+X), no con Ctrl+Enter.
--   - En DBeaver, que el script se DETENGA si hay error
--     (Preferencias > Editors > SQL Editor > SQL Processing > Error handling: Stop).
-- =====================================================================

USE multisalas;

-- >>> UNICO PARAMETRO: se respaldan y borran las ventas ANTERIORES a esta fecha
SET @fecha_corte := '2026-07-01';


-- ---------------------------------------------------------------------
-- 1) Base y tablas del historico (solo se crean la primera vez)
-- ---------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS multisalas_historico;

SET @motor := IF(
    (SELECT COUNT(*) FROM information_schema.ENGINES
      WHERE ENGINE = 'ARCHIVE' AND SUPPORT IN ('YES', 'DEFAULT')) = 1,
    'ENGINE=ARCHIVE',
    'ENGINE=InnoDB ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=8');

SET @sql := CONCAT('CREATE TABLE IF NOT EXISTS multisalas_historico.sales ', @motor, ' AS SELECT * FROM sales WHERE 1 = 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @sql := CONCAT('CREATE TABLE IF NOT EXISTS multisalas_historico.details ', @motor, ' AS SELECT * FROM details WHERE 1 = 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @sql := CONCAT('CREATE TABLE IF NOT EXISTS multisalas_historico.tickets ', @motor, ' AS SELECT * FROM tickets WHERE 1 = 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
SET @sql := CONCAT('CREATE TABLE IF NOT EXISTS multisalas_historico.audits ', @motor, ' AS SELECT * FROM audits WHERE 1 = 0');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- Enlace anulacion -> venta (el borrado deja anulaciones.sale_id en NULL)
CREATE TABLE IF NOT EXISTS multisalas_historico.anulaciones_sale (
    anulacion_id BIGINT UNSIGNED NOT NULL,
    sale_id      BIGINT UNSIGNED NOT NULL,
    archivado_at DATETIME NOT NULL,
    KEY (anulacion_id),
    KEY (sale_id)
) ENGINE=InnoDB;

-- Registro de cada ejecucion
CREATE TABLE IF NOT EXISTS multisalas_historico.purga_log (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha_corte        DATE,
    corte_sale_id      BIGINT UNSIGNED,
    borrado            TINYINT(1),
    sales              INT UNSIGNED,
    details            INT UNSIGNED,
    tickets            INT UNSIGNED,
    tickets_sin_venta  INT UNSIGNED,
    anulaciones        INT UNSIGNED,
    audits             INT UNSIGNED,
    ejecutado_at       DATETIME
) ENGINE=InnoDB;


-- ---------------------------------------------------------------------
-- 2) Punto de corte: primera venta desde @fecha_corte
--    (si no hay ventas desde esa fecha, @corte queda NULL y no se borra nada)
-- ---------------------------------------------------------------------
SET @corte := (SELECT MIN(id) FROM sales WHERE created_at >= @fecha_corte);
SET @max_audit := (SELECT IFNULL(MAX(id), 0) FROM audits);

SELECT @corte AS corte_id,
       (SELECT COUNT(*) FROM sales   WHERE id < @corte)      AS sales_a_respaldar,
       (SELECT COUNT(*) FROM details WHERE sale_id < @corte) AS details_a_respaldar,
       (SELECT COUNT(*) FROM tickets WHERE sale_id < @corte) AS tickets_a_respaldar,
       (SELECT COUNT(*) FROM tickets WHERE sale_id IS NULL AND created_at < @fecha_corte) AS tickets_sin_venta,
       (SELECT COUNT(*) FROM audits  WHERE id <= @max_audit) AS audits_a_respaldar;


-- ---------------------------------------------------------------------
-- 3) Copiar al historico (solo lo que aun no esta copiado)
-- ---------------------------------------------------------------------
SET @ya_sales   := (SELECT IFNULL(MAX(id), 0)      FROM multisalas_historico.sales);
SET @ya_details := (SELECT IFNULL(MAX(sale_id), 0) FROM multisalas_historico.details);
SET @ya_tickets := (SELECT IFNULL(MAX(sale_id), 0) FROM multisalas_historico.tickets);
SET @ya_tk_null := (SELECT IFNULL(MAX(id), 0)      FROM multisalas_historico.tickets WHERE sale_id IS NULL);
SET @ya_audits  := (SELECT IFNULL(MAX(id), 0)      FROM multisalas_historico.audits);

INSERT INTO multisalas_historico.sales
SELECT * FROM sales WHERE id < @corte AND id > @ya_sales;

-- ">=" vuelve a copiar la ultima venta por si una ejecucion anterior se corto
-- a la mitad; un duplicado en el historico no hace dano (ver RESTAURAR).
INSERT INTO multisalas_historico.details
SELECT * FROM details WHERE sale_id < @corte AND sale_id >= @ya_details;

INSERT INTO multisalas_historico.tickets
SELECT * FROM tickets WHERE sale_id < @corte AND sale_id >= @ya_tickets;

INSERT INTO multisalas_historico.tickets
SELECT * FROM tickets WHERE sale_id IS NULL AND created_at < @fecha_corte AND id > @ya_tk_null;

INSERT INTO multisalas_historico.anulaciones_sale (anulacion_id, sale_id, archivado_at)
SELECT id, sale_id, NOW() FROM anulaciones WHERE sale_id < @corte;

INSERT INTO multisalas_historico.audits
SELECT * FROM audits WHERE id <= @max_audit AND id > @ya_audits;


-- ---------------------------------------------------------------------
-- 4) Verificar: cada fila que se va a borrar debe estar en el historico
-- ---------------------------------------------------------------------
SET @n_sales   := (SELECT COUNT(*) FROM sales   WHERE id < @corte);
SET @n_details := (SELECT COUNT(*) FROM details WHERE sale_id < @corte);
SET @n_tickets := (SELECT COUNT(*) FROM tickets WHERE sale_id < @corte);
SET @n_tk_null := (SELECT COUNT(*) FROM tickets WHERE sale_id IS NULL AND created_at < @fecha_corte);
SET @n_anul    := (SELECT COUNT(*) FROM anulaciones WHERE sale_id < @corte);
SET @n_audits  := (SELECT COUNT(*) FROM audits  WHERE id <= @max_audit);

SET @v_sales := (SELECT COUNT(DISTINCT s.id) FROM multisalas_historico.sales h
                 JOIN sales s ON s.id = h.id WHERE s.id < @corte);
SET @v_details := (SELECT COUNT(DISTINCT d.id) FROM multisalas_historico.details h
                   JOIN details d ON d.id = h.id WHERE d.sale_id < @corte);
SET @v_tickets := (SELECT COUNT(DISTINCT t.id) FROM multisalas_historico.tickets h
                   JOIN tickets t ON t.id = h.id WHERE t.sale_id < @corte);
SET @v_tk_null := (SELECT COUNT(DISTINCT t.id) FROM multisalas_historico.tickets h
                   JOIN tickets t ON t.id = h.id WHERE t.sale_id IS NULL AND t.created_at < @fecha_corte);
SET @v_anul := (SELECT COUNT(DISTINCT a.id) FROM multisalas_historico.anulaciones_sale h
                JOIN anulaciones a ON a.id = h.anulacion_id AND a.sale_id = h.sale_id WHERE a.sale_id < @corte);
SET @v_audits := (SELECT COUNT(DISTINCT a.id) FROM multisalas_historico.audits h
                  JOIN audits a ON a.id = h.id WHERE a.id <= @max_audit);

SET @ok := (@corte IS NOT NULL
            AND @n_sales = @v_sales AND @n_details = @v_details AND @n_tickets = @v_tickets
            AND @n_tk_null = @v_tk_null AND @n_anul = @v_anul AND @n_audits = @v_audits);
SET @ok := IFNULL(@ok, 0);

SELECT IF(@ok = 1, 'OK: todo respaldado, se procede a borrar',
                   'ATENCION: el respaldo NO cuadra, NO se borrara nada') AS verificacion,
       @n_sales AS sales, @v_sales AS sales_en_historico,
       @n_details AS details, @v_details AS details_en_historico,
       @n_tickets AS tickets, @v_tickets AS tickets_en_historico,
       @n_tk_null AS tickets_sin_venta, @v_tk_null AS tickets_sin_venta_en_historico,
       @n_anul AS anulaciones, @v_anul AS anulaciones_en_historico,
       @n_audits AS audits, @v_audits AS audits_en_historico;


-- ---------------------------------------------------------------------
-- 5) Borrado (solo si @ok = 1; hijos primero, luego sales)
-- ---------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM details WHERE sale_id < @corte AND @ok = 1;
DELETE FROM tickets WHERE sale_id < @corte AND @ok = 1;
DELETE FROM tickets WHERE sale_id IS NULL AND created_at < @fecha_corte AND @ok = 1;
UPDATE anulaciones SET sale_id = NULL WHERE sale_id < @corte AND @ok = 1;
DELETE FROM sales   WHERE id < @corte AND @ok = 1;
-- En vez de TRUNCATE: no pierde los audits que se generen mientras corre el script
DELETE FROM audits  WHERE id <= @max_audit AND @ok = 1;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO multisalas_historico.purga_log
    (fecha_corte, corte_sale_id, borrado, sales, details, tickets, tickets_sin_venta, anulaciones, audits, ejecutado_at)
VALUES (@fecha_corte, @corte, @ok, @n_sales, @n_details, @n_tickets, @n_tk_null, @n_anul, @n_audits, NOW());


-- ---------------------------------------------------------------------
-- 6) Recuperar espacio en disco (bloquea las tablas mientras corre)
-- ---------------------------------------------------------------------
OPTIMIZE TABLE sales, details, tickets, anulaciones, audits;


-- ---------------------------------------------------------------------
-- 7) Verificacion final
-- ---------------------------------------------------------------------
SELECT IF(@ok = 1, 'BORRADO REALIZADO', 'NO SE BORRO NADA') AS resultado,
       (SELECT MIN(created_at) FROM sales) AS venta_mas_antigua,
       (SELECT COUNT(*) FROM sales) AS ventas_restantes;

SELECT table_schema, table_name, engine, table_rows,
       ROUND((data_length + index_length) / 1024 / 1024, 1) AS mb
FROM information_schema.tables
WHERE (table_schema = DATABASE() AND table_name IN ('sales', 'details', 'tickets', 'audits', 'anulaciones'))
   OR table_schema = 'multisalas_historico'
ORDER BY table_schema, mb DESC;


-- =====================================================================
-- CONSULTAR / RESTAURAR (no se ejecuta: esta comentado)
--
-- Consultar directo en el historico (sin indices: es lento pero funciona):
--   SELECT * FROM multisalas_historico.sales
--   WHERE created_at BETWEEN '2026-03-01' AND '2026-03-31 23:59:59';
--
-- Devolver ventas de un rango a la base principal. Usar INSERT IGNORE:
-- si una fila ya existe (o esta duplicada en el historico) se salta.
--
--   SET @desde := '2026-03-01', @hasta := '2026-03-31 23:59:59';
--   CREATE TEMPORARY TABLE tmp_ids ENGINE=InnoDB AS
--     SELECT DISTINCT id FROM multisalas_historico.sales WHERE created_at BETWEEN @desde AND @hasta;
--   ALTER TABLE tmp_ids ADD PRIMARY KEY (id);
--   SET FOREIGN_KEY_CHECKS = 0;
--   INSERT IGNORE INTO sales   SELECT h.* FROM multisalas_historico.sales   h JOIN tmp_ids i ON i.id = h.id;
--   INSERT IGNORE INTO details SELECT h.* FROM multisalas_historico.details h JOIN tmp_ids i ON i.id = h.sale_id;
--   INSERT IGNORE INTO tickets SELECT h.* FROM multisalas_historico.tickets h JOIN tmp_ids i ON i.id = h.sale_id;
--   UPDATE anulaciones a JOIN multisalas_historico.anulaciones_sale h ON h.anulacion_id = a.id
--     JOIN tmp_ids i ON i.id = h.sale_id SET a.sale_id = h.sale_id WHERE a.sale_id IS NULL;
--   SET FOREIGN_KEY_CHECKS = 1;
--   DROP TEMPORARY TABLE tmp_ids;
--
-- Si mas adelante se agrega una columna a sales/details/tickets/audits, este
-- script fallara en el paso 3 ANTES de borrar (las columnas no coinciden).
-- Hay que agregar la misma columna al historico, por ejemplo:
--   ALTER TABLE multisalas_historico.sales ADD COLUMN nueva_col VARCHAR(50) NULL;
-- (en la misma posicion que en la tabla principal, con AFTER otra_col).
-- =====================================================================
