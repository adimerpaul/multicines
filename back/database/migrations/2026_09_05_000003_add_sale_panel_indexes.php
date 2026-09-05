<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indices del panel de ventas (/api/saleInit).
 *
 * Las tres consultas de la carga inicial recorrian tablas completas:
 * el contador de facturas pendientes barria las 570 mil ventas, el total de
 * boletos del dia las 730 mil filas de tickets y las peliculas del dia
 * agrupaban todos los boletos de la historia.
 */
return new class extends Migration
{
    public function up()
    {
        // eventNumber: ventas pendientes de envio a SIAT.
        $this->crear('sales', 'sales_pendientes_idx', ['siatEnviado', 'siatAnulado']);
        // totalventa: boletos vendidos de una fecha de funcion.
        $this->crear('tickets', 'tickets_funcion_idx', ['fechaFuncion', 'devuelto']);
        // peliculasDelDia / hours: funciones activas de una fecha.
        $this->crear('programas', 'programas_fecha_activo_idx', ['fecha', 'activo']);
    }

    public function down()
    {
        $this->borrar('sales', 'sales_pendientes_idx');
        $this->borrar('tickets', 'tickets_funcion_idx');
        $this->borrar('programas', 'programas_fecha_activo_idx');
    }

    private function crear(string $tabla, string $nombre, array $columnas): void
    {
        if (!Schema::hasTable($tabla) || $this->existe($tabla, $nombre)) {
            return;
        }

        Schema::table($tabla, function ($table) use ($nombre, $columnas) {
            $table->index($columnas, $nombre);
        });
    }

    private function borrar(string $tabla, string $nombre): void
    {
        if (!Schema::hasTable($tabla) || !$this->existe($tabla, $nombre)) {
            return;
        }

        Schema::table($tabla, function ($table) use ($nombre) {
            $table->dropIndex($nombre);
        });
    }

    private function existe(string $tabla, string $nombre): bool
    {
        return count(DB::select("SHOW INDEX FROM `{$tabla}` WHERE Key_name = ?", [$nombre])) > 0;
    }
};
