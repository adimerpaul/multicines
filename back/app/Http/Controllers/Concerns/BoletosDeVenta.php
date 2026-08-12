<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Client;
use App\Models\Momentaneo;
use App\Models\Programa;
use App\Models\Sale;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Genera los boletos de una venta a partir de los asientos momentaneos.
 *
 * Antes este bloque estaba repetido en seis lugares y en todos calculaba
 * el numero de boleto como COUNT(*) + 1 dentro del foreach, pero el insert
 * recien ocurria al terminar el foreach: todos los asientos de una misma
 * funcion salian con el mismo numero y el mismo numboc. Ademas dos cajas
 * vendiendo la misma funcion al mismo tiempo leian el mismo COUNT.
 */
trait BoletosDeVenta
{
    /**
     * Inserta los boletos de la venta y limpia los asientos momentaneos del
     * usuario, de modo que cada venta se lleve solo lo suyo.
     *
     * @return \Illuminate\Database\Eloquent\Collection boletos de la venta
     */
    protected function generarBoletos(Sale $sale, Client $client, User $user)
    {
        $lock = 'multisalas_boletos';
        $tomado = DB::selectOne('SELECT GET_LOCK(?, 10) AS tomado', [$lock]);
        $conLock = $tomado && intval($tomado->tomado) === 1;

        try {
            $momentaneos = Momentaneo::where('user_id', $user->id)->get();
            $data = [];
            $ultimoNumero = []; // programa_id => ultimo numero de boleto usado

            foreach ($momentaneos as $m) {
                $programa = Programa::find($m->programa_id);
                if (!$programa) {
                    continue;
                }

                $ocupado = Ticket::where('programa_id', $m->programa_id)
                    ->where("fila", $m->fila)
                    ->where("devuelto", 0)
                    ->where("columna", $m->columna)
                    ->where("letra", $m->letra)
                    ->where("sala_id", $programa->sala->id)
                    ->count();
                if ($ocupado) {
                    continue;
                }

                if (!isset($ultimoNumero[$m->programa_id])) {
                    $ultimoNumero[$m->programa_id] = Ticket::where('programa_id', $m->programa_id)->count();
                }
                $numBoleto = ++$ultimoNumero[$m->programa_id];

                $data[] = [
                    "numboc" => $programa->sala->nro . $programa->sala->id . date('Ymd', strtotime($programa->fecha)) . $programa->nroFuncion . $programa->price->serie . '-' . $numBoleto,
                    "numero" => $numBoleto,
                    "fecha" => now(),
                    "numeroFuncion" => $programa->nroFuncion,
                    "nombreSala" => $programa->sala->nombre,
                    "serieTarifa" => $programa->price->serie,
                    "fechaFuncion" => $programa->fecha,
                    "horaFuncion" => $programa->horaInicio,
                    "fila" => $m->fila,
                    "columna" => $m->columna,
                    "letra" => $m->letra,
                    "costo" => $programa->price->precio,
                    "titulo" => $m->pelicula,
                    "devuelto" => "0",
                    "idCupon" => "",
                    "tarjeta" => "",
                    "credito" => "",
                    "promo" => $m->promo,
                    "client_id" => $client->id,
                    "programa_id" => $programa->id,
                    "pelicula_id" => $m->pelicula_id,
                    "sale_id" => $sale->id,
                    "price_id" => $programa->price->id,
                    "sala_id" => $programa->sala->id,
                    "user_id" => $user->id,
                ];
            }

            if (count($data)) {
                Ticket::insert($data);
            }

            // Los asientos ya son boletos: se sueltan para que la siguiente
            // venta del usuario no los vuelva a arrastrar.
            Momentaneo::where('user_id', $user->id)->delete();
        } finally {
            if ($conLock) {
                DB::selectOne('SELECT RELEASE_LOCK(?) AS liberado', [$lock]);
            }
        }

        return Ticket::where('sale_id', $sale->id)->get();
    }

    /**
     * Detalle de factura de la venta (una linea por funcion).
     */
    protected function generarDetalles(Sale $sale, array $detalleVenta): void
    {
        $dataDetail = [];
        foreach ($detalleVenta as $detalle) {
            $dataDetail[] = [
                'actividadEconomica' => "590000",
                'codigoProductoSin' => "99100",
                'cantidad' => $detalle['cantidad'],
                'precioUnitario' => $detalle['precio'],
                'subTotal' => $detalle['subtotal'],
                'sale_id' => $sale->id,
                'programa_id' => $detalle['programa_id'],
                'pelicula_id' => $detalle['pelicula_id'],
                'descripcion' => $detalle['pelicula'],
            ];
        }

        if (count($dataDetail)) {
            \App\Models\Detail::insert($dataDetail);
        }
    }
}
