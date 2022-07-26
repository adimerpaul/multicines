<?php

namespace App\Http\Controllers;

use App\Models\Cui;
use App\Http\Requests\StoreCuiRequest;
use App\Http\Requests\UpdateCuiRequest;

class CuiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreCuiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCuiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cui  $cui
     * @return \Illuminate\Http\Response
     */
    public function show(Cui $cui)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cui  $cui
     * @return \Illuminate\Http\Response
     */
    public function edit(Cui $cui)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCuiRequest  $request
     * @param  \App\Models\Cui  $cui
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCuiRequest $request, Cui $cui)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cui  $cui
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cui $cui)
    {
        //
    }
}
