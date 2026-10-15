<?php

namespace App\Http\Controllers;

use App\Models\WebCliente;
use App\Services\FirebaseIdToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Login de clientes de la web publica. Firebase (Google) solo se usa para
 * comprobar la identidad una vez; despues la sesion es de Laravel y los
 * datos del cliente quedan en la tabla local web_clientes.
 */
class CineAuthController extends Controller
{
    public const SESSION_KEY = 'cine_cliente_id';

    /** Cliente logueado en la sesion actual (o null). */
    public static function clienteActual(Request $request): ?WebCliente
    {
        $id = $request->session()->get(self::SESSION_KEY);
        if (!$id) {
            return null;
        }
        $cliente = WebCliente::where('estado', 'ACTIVO')->find($id);
        if (!$cliente) {
            $request->session()->forget(self::SESSION_KEY);
        }

        return $cliente;
    }

    public function login(Request $request)
    {
        $data = $request->validate(['id_token' => 'required|string|max:4096']);

        try {
            $claims = (new FirebaseIdToken((string) config('services.firebase.project_id')))->verificar($data['id_token']);
        } catch (RuntimeException $e) {
            Log::warning('Login web rechazado: ' . $e->getMessage());
            return response()->json(['message' => 'No se pudo verificar tu cuenta de Google'], 401);
        }

        $cliente = WebCliente::withTrashed()->firstOrNew(['firebase_uid' => $claims['sub']]);
        if ($cliente->trashed()) {
            $cliente->restore();
        }
        if ($cliente->exists && $cliente->estado !== 'ACTIVO') {
            return response()->json(['message' => 'Tu cuenta esta deshabilitada. Acercate a boleteria.'], 403);
        }

        $cliente->fill([
            'proveedor' => $claims['firebase']['sign_in_provider'] ?? 'google.com',
            'email' => isset($claims['email']) ? mb_strtolower($claims['email']) : $cliente->email,
            'email_verificado' => (bool) ($claims['email_verified'] ?? false),
            // El nombre/foto de Google solo se toman si el cliente no los cambio
            'nombre' => $cliente->nombre ?: mb_substr((string) ($claims['name'] ?? ''), 0, 255) ?: null,
            'foto' => mb_substr((string) ($claims['picture'] ?? $cliente->foto), 0, 500) ?: null,
            'ultimo_login_at' => now(),
            'ultimo_login_ip' => $request->ip(),
        ]);
        $cliente->logins = (int) $cliente->logins + 1;
        $cliente->save();

        $request->session()->migrate(true);
        $request->session()->put(self::SESSION_KEY, $cliente->id);

        return response()->json(['cliente' => $cliente->publico()]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->migrate(true);

        return response()->json(['ok' => true]);
    }

    /** Guarda los datos de facturacion para autocompletar la siguiente compra. */
    public function guardarDatos(Request $request)
    {
        $cliente = self::clienteActual($request);
        if (!$cliente) {
            return response()->json(['message' => 'Inicia sesion para continuar', 'login' => true], 401);
        }

        $data = $request->validate([
            'nit' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:5',
            'nombre' => 'nullable|string|max:120',
            'telefono' => 'nullable|string|max:30',
        ]);

        $cliente->update([
            'nit' => $data['nit'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'razon_social' => $data['nombre'] ?? null,
            'telefono' => $data['telefono'] ?? $cliente->telefono,
        ]);

        return response()->json(['cliente' => $cliente->publico()]);
    }
}
