<?php

namespace App\Http\Controllers;

use App\Models\Rubro;
use App\Http\Requests\StoreRubroRequest;
use App\Http\Requests\UpdateRubroRequest;

class RubroController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return Rubro::all();
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
     * @param  \App\Http\Requests\StoreRubroRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRubroRequest $request)
    {
        //
        $rubro= new Rubro;
        $rubro->nombre=$request->nombre;
        $rubro->descripcion=$request->descripcion;
        $rubro->imagen=$request->imagen;
        $rubro->color=$request->color;
        $rubro->save();
        return $rubro;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rubro  $rubro
     * @return \Illuminate\Http\Response
     */
    public function show(Rubro $rubro)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rubro  $rubro
     * @return \Illuminate\Http\Response
     */
    public function edit(Rubro $rubro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRubroRequest  $request
     * @param  \App\Models\Rubro  $rubro
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRubroRequest $request, Rubro $rubro)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rubro  $rubro
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rubro $rubro)
    {
        //
    }
}
