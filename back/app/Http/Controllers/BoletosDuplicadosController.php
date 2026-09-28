<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Revision de butacas vendidas dos o mas veces en la misma funcion.
 * Solo para usuarios con el permiso 26 (ver todas las ventas).
 */
class BoletosDuplicadosController extends Controller
{
    private function autorizar(Request $request)
    {
        abort_unless(
            $request->user()->permisos()->where('permisos.id', 26)->exists(),
            403,
            'No tiene permiso para revisar boletos duplicados'
        );
    }

    /**
     * Funciones de un dia con la cantidad de boletos vendidos y
     * cuantas butacas estan vendidas mas de una vez.
     */
    public function funciones(Request $request)
    {
        $this->autorizar($request);
        $request->validate(['fecha' => 'required|date']);

        return DB::select("
            SELECT p.id, p.horaInicio, p.fecha, m.id as movie_id, m.nombre, m.formato, m.imagen,
                   s.nro as sala, s.nombre as salaNombre,
                   COALESCE(v.vendidos, 0) as vendidos,
                   COALESCE(v.butacas, 0) as butacas
            FROM programas p
            INNER JOIN movies m ON m.id = p.movie_id
            INNER JOIN salas s ON s.id = p.sala_id
            LEFT JOIN (
                SELECT t.programa_id,
                       COUNT(*) as vendidos,
                       COUNT(DISTINCT t.fila, t.columna, t.letra) as butacas
                FROM tickets t
                INNER JOIN programas p2 ON p2.id = t.programa_id
                INNER JOIN sales sa ON sa.id = t.sale_id
                WHERE p2.fecha = ? AND t.devuelto = 0 AND t.deleted_at IS NULL
                  AND sa.deleted_at IS NULL AND sa.siatAnulado = 0
                GROUP BY t.programa_id
            ) v ON v.programa_id = p.id
            WHERE p.fecha = ? AND p.deleted_at IS NULL
            ORDER BY m.nombre, p.horaInicio", [$request->fecha, $request->fecha]);
    }

    /**
     * Todos los boletos vendidos de una funcion con el comprador,
     * marcando las butacas que estan vendidas mas de una vez.
     */
    public function boletos(Request $request)
    {
        $this->autorizar($request);
        $request->validate(['id' => 'required|integer']);

        $boletos = DB::select("
            SELECT t.id, t.numboc, t.fila, t.columna, t.letra, t.costo, t.created_at,
                   sa.id as sale_id, sa.numeroFactura, sa.venta, sa.montoTotal, sa.fechaEmision,
                   sa.vip, sa.cortesia, sa.credito, sa.pagoQr,
                   c.nombreRazonSocial, c.numeroDocumento, c.complemento,
                   u.name as usuario
            FROM tickets t
            INNER JOIN sales sa ON sa.id = t.sale_id
            LEFT JOIN clients c ON c.id = sa.client_id
            LEFT JOIN users u ON u.id = sa.user_id
            WHERE t.programa_id = ? AND t.devuelto = 0 AND t.deleted_at IS NULL
              AND sa.deleted_at IS NULL AND sa.siatAnulado = 0
            ORDER BY t.letra, t.columna, t.id", [$request->id]);

        $conteo = [];
        foreach ($boletos as $b) {
            $clave = $b->letra . '-' . $b->fila . '-' . $b->columna;
            $conteo[$clave] = ($conteo[$clave] ?? 0) + 1;
        }
        foreach ($boletos as $b) {
            $b->veces = $conteo[$b->letra . '-' . $b->fila . '-' . $b->columna];
            $b->duplicado = $b->veces > 1;
        }

        // Plano de la sala para dibujar las butacas como en la venta
        $sala = DB::selectOne("
            SELECT s.id, s.nro, s.nombre, s.filas, s.columnas, s.capacidad
            FROM programas p
            INNER JOIN salas s ON s.id = p.sala_id
            WHERE p.id = ?", [$request->id]);
        $seats = $sala ? DB::select("
            SELECT fila, columna, letra, activo
            FROM seats
            WHERE sala_id = ? AND deleted_at IS NULL", [$sala->id]) : [];

        return [
            'sala' => $sala,
            'seats' => $seats,
            'boletos' => $boletos,
        ];
    }
}
