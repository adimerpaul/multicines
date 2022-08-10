<?php

namespace App\Http\Controllers;

use App\Models\EventoSignificativo;
use App\Http\Requests\StoreEventoSignificativoRequest;
use App\Http\Requests\UpdateEventoSignificativoRequest;

class EventoSignificativoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return EventoSignificativo::all();
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
     * @param  \App\Http\Requests\StoreEventoSignificativoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEventoSignificativoRequest $request)
    {
        return new EventoSignificativo($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EventoSignificativo  $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function show(EventoSignificativo $eventoSignificativo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EventoSignificativo  $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function edit(EventoSignificativo $eventoSignificativo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEventoSignificativoRequest  $request
     * @param  \App\Models\EventoSignificativo  $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEventoSignificativoRequest $request, EventoSignificativo $eventoSignificativo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EventoSignificativo  $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function destroy(EventoSignificativo $eventoSignificativo)
    {
        //
    }
}
