<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CineToken;
use App\Models\Sale;
use App\Services\VentaWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Compra de entradas del cliente web (logueado con Google) y "Mis compras".
 * La compra en curso vive en la sesion; los asientos quedan retenidos en
 * momentaneos hasta que se paga, se cancela o vence la reserva.
 */
class CineCompraController extends Controller
{
    const SESSION_COMPRA = 'cine_compra';

    public function __construct(private VentaWeb $ventaWeb)
    {
    }

    /** Paso "Ir a pagar": valida datos y retiene los asientos. */
    public function iniciar(Request $request)
    {
        $cliente = CineAuthController::clienteActual($request);
        if (!$cliente) {
            return response()->json(['message' => 'Inicia sesion para continuar', 'login' => true], 401);
        }

        $data = $request->validate([
            'programa_id' => 'required|integer|min:1',
            'asientos' => 'required|array|min:1|max:' . VentaWeb::MAX_ASIENTOS,
            'asientos.*' => ['required', 'string', 'regex:/^[A-Za-z]{1,2}\d{1,3}$/'],
            'nit' => ['required', 'string', 'regex:/^\d{1,20}$/'],
            'complemento' => ['nullable', 'string', 'regex:/^[A-Za-z0-9]{0,5}$/'],
            'nombre' => 'required|string|max:120',
            'email' => 'nullable|email|max:120',
        ]);

        // Una sola compra en curso por sesion: la anterior se suelta
        $this->ventaWeb->liberar(optional($request->session()->get(self::SESSION_COMPRA))['token'] ?? null);
        $request->session()->forget(self::SESSION_COMPRA);

        try {
            $compra = $this->ventaWeb->reservar($cliente, (int) $data['programa_id'], $data['asientos'], [
                'nit' => $data['nit'],
                'complemento' => $data['complemento'] ?? '',
                'nombre' => $data['nombre'],
                'email' => $data['email'] ?? '',
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        $request->session()->put(self::SESSION_COMPRA, $compra);

        return response()->json([
            'token' => $compra['token'],
            'total' => $compra['total'],
            'expiraEn' => max(0, $compra['expira'] - time()),
            'pagoDemo' => (bool) config('services.cine.pago_demo'),
        ]);
    }

    /** Suelta los asientos (volver atras, tiempo agotado). */
    public function cancelar(Request $request)
    {
        $compra = $request->session()->get(self::SESSION_COMPRA);
        if ($compra && hash_equals((string) $compra['token'], (string) $request->input('token'))) {
            $this->ventaWeb->liberar($compra['token']);
            $request->session()->forget(self::SESSION_COMPRA);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Pago simulado (solo con CINE_PAGO_DEMO=true). Cuando se conecte el QR
     * de Baneco, su confirmacion llamara a VentaWeb::confirmar() igual que aqui.
     */
    public function pagarDemo(Request $request)
    {
        if (!config('services.cine.pago_demo')) {
            return response()->json(['message' => 'El pago de prueba está desactivado'], 403);
        }

        $cliente = CineAuthController::clienteActual($request);
        if (!$cliente) {
            return response()->json(['message' => 'Inicia sesion para continuar', 'login' => true], 401);
        }

        $compra = $request->session()->get(self::SESSION_COMPRA);
        if (!$compra || !hash_equals((string) $compra['token'], (string) $request->input('token'))) {
            return response()->json(['message' => 'No hay una compra en curso. Vuelve a elegir tus asientos.'], 409);
        }

        try {
            $sale = $this->ventaWeb->confirmar($compra, $cliente, [
                'qrId' => 'WEB-DEMO-' . substr($compra['token'], 0, 8),
                'qrTransactionId' => 'DEMO',
            ]);
        } catch (RuntimeException $e) {
            $request->session()->forget(self::SESSION_COMPRA);
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            Log::error('Venta web: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'No se pudo registrar la compra. Tus asientos siguen reservados unos minutos; intenta de nuevo.'], 500);
        }

        $request->session()->forget(self::SESSION_COMPRA);

        return response()->json(['entrada' => $this->entradaVista($this->cargarVenta($sale->id))]);
    }

    public function misCompras(Request $request)
    {
        $cliente = CineAuthController::clienteActual($request);
        $compras = collect();

        if ($cliente) {
            $compras = Sale::where('web_cliente_id', $cliente->id)
                ->where('canal', 'WEB')
                ->orderByDesc('id')
                ->limit(50)
                ->pluck('id')
                ->map(fn($id) => $this->entradaVista($this->cargarVenta($id)));
        }

        return view('cine.mis-compras', [
            'cineToken' => CineToken::issue($request),
            'activo' => 'compras',
            'cliente' => $cliente,
            'compras' => $compras,
        ]);
    }

    private function cargarVenta(int $id): Sale
    {
        return Sale::with([
            'client',
            'tickets' => fn($q) => $q->orderBy('letra')->orderByDesc('columna'),
        ])->findOrFail($id);
    }

    /** Datos de la entrada para el navegador (ticket + factura/recibo). */
    private function entradaVista(Sale $sale): array
    {
        $tickets = $sale->tickets;
        $primero = $tickets->first();
        $programa = $primero ? \App\Models\Programa::with('movie', 'sala', 'webMovies')->find($primero->programa_id) : null;
        $web = $programa ? $programa->webMovies->first() : null;
        $poster = optional($web)->imagen ?: optional(optional($programa)->movie)->imagen;
        $hora = $primero ? strtotime($primero->horaFuncion) : null;
        $activos = $tickets->where('devuelto', '0');

        if ($sale->siatAnulado) {
            $estado = 'ANULADA';
        } elseif ($sale->verificado) {
            $estado = 'USADA';
        } elseif ($hora && $hora < time() - 3 * 3600) {
            $estado = 'VENCIDA';
        } else {
            $estado = 'VIGENTE';
        }

        return [
            'id' => $sale->id,
            'codigo' => $sale->codigo_entrada,
            'qr' => $sale->codigo_entrada ? $this->ventaWeb->qrDataUri($sale->codigo_entrada) : null,
            'estado' => $estado,
            'verificadoAt' => $sale->verificado_at ? date('d/m/Y H:i', strtotime($sale->verificado_at)) : null,
            'titulo' => optional($web)->titulo ?: optional($primero)->titulo,
            'poster' => asset('imagen/' . ($poster ?: 'default.jpg')),
            'fecha' => $hora ? $this->fechaLarga($hora) : '',
            'hora' => $hora ? date('H:i', $hora) . ' · ' . (optional(optional($programa)->movie)->formato ?: '2D') : '',
            'sala' => optional($primero)->nombreSala,
            'asientos' => $activos->map(fn($t) => $t->letra . $t->columna)->values()->join(', '),
            'cantidad' => $activos->count(),
            'precio' => (float) optional($primero)->costo,
            'total' => (float) $sale->montoTotal,
            'nit' => optional($sale->client)->numeroDocumento . (optional($sale->client)->complemento ? '-' . $sale->client->complemento : ''),
            'nombre' => optional($sale->client)->nombreRazonSocial,
            'email' => optional($sale->client)->email,
            'emision' => date('d/m/Y H:i', strtotime($sale->fechaEmision)),
            'numero' => $sale->id,
        ];
    }

    private function fechaLarga(int $t): string
    {
        return CineController::DOW[date('w', $t)] . ' ' . date('j', $t) . ' ' . CineController::MESES[date('n', $t) - 1] . ' ' . date('Y', $t);
    }
}
