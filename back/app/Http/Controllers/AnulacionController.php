<?php

namespace App\Http\Controllers;

use App\Models\Anulacion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnulacionController extends Controller
{
    public function index(Request $request)
    {
        $estado   = $request->query('estado'); // Pendiente|Autorizado|Anulado|Rechazado
        $search   = trim((string) $request->query('search', ''));
        $fi       = $request->query('fi'); // YYYY-MM-DD
        $ff       = $request->query('ff'); // YYYY-MM-DD
        $perPage  = (int) $request->query('per_page', 25);

        $q = Anulacion::with(['user','userAutoriza','userAnulacion','sale'])
            ->when($estado, fn($qq) => $qq->where('estado', $estado))
            ->when($fi, fn($qq) => $qq->whereDate('fecha', '>=', $fi))
            ->when($ff, fn($qq) => $qq->whereDate('fecha', '<=', $ff))
            ->when($search, function($qq) use ($search) {
                $qq->where(function($w) use ($search) {
                    $w->where('cajero', 'like', "%{$search}%")
                        ->orWhere('motivo', 'like', "%{$search}%")
                        ->orWhere('seccion','like', "%{$search}%")
                        ->orWhere('detalle','like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        if ($perPage === 0) {
            return $q->get();
        }
        return $q->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
//            'fecha'   => ['required','date'],
//            'cajero'  => ['required','string','max:255'],
//            'monto'   => ['required','numeric','min:0'],
            'motivo'  => ['nullable','string','max:255'],
            'sale_id' => ['nullable','integer','exists:sales,id'],
            'seccion' => ['nullable','string','max:255'],
            'detalle' => ['nullable','string','max:255'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['estado']  = 'Pendiente';
        $data['fecha']   = now();
        $data['cajero']  = $request->user()->name;
        $data['monto']   = $data['sale_id'] ? optional(\App\Models\Sale::find($data['sale_id']))->montoTotal : 0;

        $anulacion = Anulacion::create($data);
        return response()->json($anulacion->load(['user','userAutoriza','userAnulacion','sale']), 201);
    }

    public function autorizar(Request $request, Anulacion $anulacion)
    {
        if ($anulacion->estado !== 'Pendiente') {
            return response()->json(['message' => 'Solo se pueden autorizar registros en estado Pendiente'], 422);
        }
        if ($request->user()->id === $anulacion->user_id) {
            return response()->json(['message' => 'Quien solicita no puede autorizar su propia anulación'], 422);
        }

        $request->validate([
            'motivo' => ['nullable','string','max:255'],
        ]);

        $anulacion->update([
            'estado'             => 'Autorizado',
            'user_autoriza_id'   => $request->user()->id,
            'motivo'             => $request->input('motivo', $anulacion->motivo),
        ]);

        return $anulacion->load(['user','userAutoriza','userAnulacion','sale']);
    }

    public function anular(Request $request, Anulacion $anulacion)
    {
        if ($anulacion->estado !== 'Autorizado') {
            return response()->json(['message' => 'Solo se pueden anular registros en estado Autorizado'], 422);
        }
        if ($request->user()->id === $anulacion->user_autoriza_id) {
            return response()->json(['message' => 'La misma persona que autorizó no puede ejecutar la anulación'], 422);
        }

        $request->validate([
            'detalle' => ['nullable','string','max:255'],
        ]);

        $anulacion->update([
            'estado'             => 'Anulado',
            'user_anulacion_id'  => $request->user()->id,
            'detalle'            => $request->input('detalle', $anulacion->detalle),
        ]);

        // Aquí podrías ejecutar la lógica contable: reversar sale_id, etc.
        return $anulacion->load(['user','userAutoriza','userAnulacion','sale']);
    }

    public function rechazar(Request $request, Anulacion $anulacion)
    {
        if ($anulacion->estado !== 'Pendiente') {
            return response()->json(['message' => 'Solo se pueden rechazar registros en estado Pendiente'], 422);
        }
        if ($request->user()->id === $anulacion->user_id) {
            return response()->json(['message' => 'Quien solicita no puede rechazar su propia anulación'], 422);
        }

        $request->validate([
            'motivo' => ['nullable','string','max:255'],
        ]);

        $anulacion->update([
            'estado'             => 'Rechazado',
            'user_autoriza_id'   => $request->user()->id, // quien rechaza queda como “autoriza” (decisor)
            'motivo'             => $request->input('motivo', $anulacion->motivo),
        ]);

        return $anulacion->load(['user','userAutoriza','userAnulacion','sale']);
    }
}
