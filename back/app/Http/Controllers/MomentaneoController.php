<?php

namespace App\Http\Controllers;

use App\Models\Momentaneo;
use App\Http\Requests\StoreMomentaneoRequest;
use App\Http\Requests\UpdateMomentaneoRequest;
use App\Services\AuditoriaButacas;
use Illuminate\Http\Request;

class MomentaneoController extends Controller
{
    public function __construct(private AuditoriaButacas $auditoria)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Momentaneo::where('user_id',$request->user()->id)->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMomentaneoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMomentaneoRequest $request)
    {
        $datos = [
            'user_id' => $request->user()->id,
            'programa_id' => $request->programa_id,
            'fila' => $request->fila,
            'columna' => $request->columna,
            'letra' => $request->letra,
            'fecha' => $request->fecha,
            'precio' => $request->precio,
            'pelicula' => $request->pelicula,
            'pelicula_id' => $request->pelicula_id,
            'promo' => $request->promo ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // insertOrIgnore + indice unico momentaneos_butaca_unique: si la butaca ya
        // esta tomada (por este cajero o por otro) no se crea una segunda reserva.
        // Se devuelve 1 para que el front refresque la sala.
        if (!Momentaneo::insertOrIgnore($datos)) {
            // Queda registrado el clic que no reservo nada: es el caso en que
            // el cajero marca una butaca que otra caja ya tenia tomada.
            $this->auditoria->ocupada((int) $request->programa_id, $datos);

            return 1;
        }

        $this->auditoria->seleccionada((int) $request->programa_id, $datos);

        // Se devuelve el registro creado, con la sala y el numero de funcion,
        // para que el front lo muestre en el resumen sin pedir la lista otra vez.
        return Momentaneo::conFuncion()
            ->where('momentaneos.programa_id', $request->programa_id)
            ->where('momentaneos.fila', $request->fila)
            ->where('momentaneos.columna', $request->columna)
            ->where('momentaneos.letra', $request->letra)
            ->first();
    }
    public function momentaneoDelete(Request $request)
    {
        // Se leen antes de borrar: despues del delete ya no se sabe que solto.
        $butacas = Momentaneo::where("user_id",$request->user()->id)
            ->where("programa_id",$request->programa_id)
            ->where("fila",$request->fila)
            ->where("columna",$request->columna)
            ->where("letra",$request->letra)
            ->get();

        Momentaneo::whereIn('id', $butacas->pluck('id'))->delete();
        $this->auditoria->liberadas($butacas, 'clic del cajero');
    }
    public function momentaneoDeleteUser(Request $request)
    {
        $butacas = Momentaneo::where("user_id",$request->user()->id)
            ->where("programa_id",$request->programa_id)
            ->get();

        Momentaneo::whereIn('id', $butacas->pluck('id'))->delete();
        $this->auditoria->liberadas($butacas, 'cambio de funcion');
    }
    public function momentaneoDeleteAll(Request $request)
    {
        $butacas = Momentaneo::where("user_id",$request->user()->id)->get();

        Momentaneo::whereIn('id', $butacas->pluck('id'))->delete();
        $this->auditoria->liberadas($butacas, 'venta cancelada o terminada');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Momentaneo  $momentaneo
     * @return \Illuminate\Http\Response
     */
    public function show(Momentaneo $momentaneo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Momentaneo  $momentaneo
     * @return \Illuminate\Http\Response
     */
    public function edit(Momentaneo $momentaneo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMomentaneoRequest  $request
     * @param  \App\Models\Momentaneo  $momentaneo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMomentaneoRequest $request, Momentaneo $momentaneo)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Momentaneo  $momentaneo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Momentaneo $momentaneo)
    {

    }
}
