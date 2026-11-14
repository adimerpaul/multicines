<?php

namespace App\Http\Controllers;

use App\Models\SiatToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiatTokenController extends Controller
{
    const PERMISO = 33;

    private function autorizar(Request $request)
    {
        abort_unless(
            $request->user()->permisos()->where('permisos.id', self::PERMISO)->exists(),
            403,
            'No tiene permiso para gestionar el token del SIAT'
        );
    }

    public function index(Request $request)
    {
        $this->autorizar($request);
        return SiatToken::with('user:id,name')->orderBy('id', 'desc')->get();
    }

    public function store(Request $request)
    {
        $this->autorizar($request);
        $request->validate([
            'token' => 'required|string',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $token = trim(trim($request->token), '"');
        $datos = SiatToken::datosDesdeJwt($token);
        if (!$datos) {
            return response()->json(['message' => 'El texto no es un token válido del SIAT (JWT)'], 422);
        }
        if ($datos['fechaVigencia'] && strtotime($datos['fechaVigencia']) < time()) {
            return response()->json(['message' => 'El token ya está vencido (venció el ' . date('d/m/Y', strtotime($datos['fechaVigencia'])) . ')'], 422);
        }
        if (SiatToken::where('token', $token)->exists()) {
            return response()->json(['message' => 'Este token ya está registrado'], 422);
        }

        $siatToken = SiatToken::create(array_merge($datos, [
            'token' => $token,
            'descripcion' => $request->descripcion,
            'activo' => false,
            'user_id' => $request->user()->id,
        ]));

        if (filter_var($request->input('activar', false), FILTER_VALIDATE_BOOLEAN)) {
            $this->activarToken($siatToken);
        }

        return $siatToken->fresh('user:id,name');
    }

    public function activar(Request $request, SiatToken $siatToken)
    {
        $this->autorizar($request);
        if ($siatToken->fechaVigencia && strtotime($siatToken->fechaVigencia) < time()) {
            return response()->json(['message' => 'El token está vencido, no se puede activar'], 422);
        }
        $this->activarToken($siatToken);
        return $siatToken->fresh('user:id,name');
    }

    public function destroy(Request $request, SiatToken $siatToken)
    {
        $this->autorizar($request);
        if ($siatToken->activo) {
            return response()->json(['message' => 'No se puede eliminar el token activo'], 422);
        }
        $siatToken->delete();
        return response()->json(['message' => 'Token eliminado']);
    }

    private function activarToken(SiatToken $siatToken)
    {
        DB::transaction(function () use ($siatToken) {
            SiatToken::where('id', '!=', $siatToken->id)->update(['activo' => false]);
            $siatToken->activo = true;
            $siatToken->fechaActivacion = now();
            $siatToken->save();
        });
        SiatToken::limpiarCache();
    }
}
