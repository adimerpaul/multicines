<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\ReservaFacturaException;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

/**
 * Reserva el correlativo de factura de forma atomica.
 *
 * Antes cada venta calculaba MAX(numeroFactura) y ademas adivinaba el proximo
 * id de la venta (ultimo id + 1) para nombrar el XML temporal. Si dos cajas
 * facturaban al mismo tiempo las dos obtenian el mismo numero y el mismo
 * archivo temporal, por lo que una pisaba a la otra y se terminaba enviando o
 * imprimiendo la factura de otra venta.
 */
trait ReservaFactura
{
    /**
     * Ejecuta $crearVenta dentro de un lock de MySQL pasandole el siguiente
     * numeroFactura del cufd/tipo indicado. El callback debe crear y guardar la
     * venta, de modo que al soltar el lock el correlativo ya este ocupado por
     * una fila real y ninguna otra caja lo pueda tomar.
     *
     * @param  callable(int): \App\Models\Sale  $crearVenta
     * @return \App\Models\Sale
     *
     * @throws \App\Exceptions\ReservaFacturaException si no se logra tomar el lock
     */
    protected function reservarVenta(string $cufd, string $tipo, callable $crearVenta): Sale
    {
        $lock = 'multisalas_factura_' . md5($cufd . '|' . $tipo);
        $tomado = DB::selectOne('SELECT GET_LOCK(?, 10) AS tomado', [$lock]);

        if (!$tomado || intval($tomado->tomado) !== 1) {
            throw new ReservaFacturaException('Otra caja esta facturando en este momento, vuelva a intentar');
        }

        try {
            $max = Sale::where('cufd', $cufd)->where('tipo', $tipo)->max('numeroFactura');

            return $crearVenta(intval($max) + 1);
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS liberado', [$lock]);
        }
    }
}
