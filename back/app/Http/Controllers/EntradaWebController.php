<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

/**
 * Validacion en la puerta de la sala de las entradas compradas por la web
 * (codigo del QR = sales.codigo_entrada). Cada entrada se verifica una sola vez.
 */
class EntradaWebController extends Controller
{
    public function buscar(Request $request)
    {
        $sale = $this->venta($request);
        if (!$sale) {
            return response()->json(['message' => 'No existe una entrada con ese código'], 404);
        }

        return response()->json($this->vista($sale));
    }

    public function verificar(Request $request)
    {
        $sale = $this->venta($request);
        if (!$sale) {
            return response()->json(['message' => 'No existe una entrada con ese código'], 404);
        }
        if ($sale->siatAnulado) {
            return response()->json(['message' => 'La venta está ANULADA, no puede ingresar'] + $this->vista($sale), 409);
        }

        // Atomico: si dos porteros escanean a la vez, solo uno la marca
        $marcada = Sale::where('id', $sale->id)
            ->where('verificado', false)
            ->update([
                'verificado' => true,
                'verificado_at' => now(),
                'verificado_user_id' => $request->user()->id,
            ]);

        $sale->refresh();
        if (!$marcada) {
            return response()->json(['message' => 'Esta entrada YA FUE USADA'] + $this->vista($sale), 409);
        }

        return response()->json(['message' => 'Entrada verificada, puede ingresar'] + $this->vista($sale));
    }

    private function venta(Request $request): ?Sale
    {
        $data = $request->validate(['codigo' => 'required|string|max:40']);
        // El lector de QR puede mandar espacios o minusculas
        $codigo = strtoupper(preg_replace('/\s+/', '', $data['codigo']));

        return Sale::with(['client', 'tickets', 'details'])
            ->where('codigo_entrada', $codigo)
            ->first();
    }

    private function vista(Sale $sale): array
    {
        $tickets = $sale->tickets;
        $primero = $tickets->first();
        $verificador = $sale->verificado_user_id ? \App\Models\User::find($sale->verificado_user_id) : null;

        return [
            'sale_id' => $sale->id,
            'codigo' => $sale->codigo_entrada,
            'estado' => $sale->siatAnulado ? 'ANULADA' : ($sale->verificado ? 'USADA' : 'VIGENTE'),
            'verificado' => (bool) $sale->verificado,
            'verificado_at' => $sale->verificado_at,
            'verificado_por' => optional($verificador)->name,
            'pelicula' => optional($primero)->titulo,
            'sala' => optional($primero)->nombreSala,
            'fecha_funcion' => optional($primero)->fechaFuncion,
            'hora_funcion' => $primero ? date('H:i', strtotime($primero->horaFuncion)) : null,
            'asientos' => $tickets->where('devuelto', '0')->map(fn($t) => $t->letra . $t->columna)->values(),
            'cantidad' => $tickets->where('devuelto', '0')->count(),
            'cliente' => optional($sale->client)->nombreRazonSocial,
            'nit' => optional($sale->client)->numeroDocumento,
            'total' => (float) $sale->montoTotal,
            'fecha_compra' => $sale->fechaEmision,
        ];
    }
}
