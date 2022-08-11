<?php

namespace App\Http\Controllers;

use App\Models\SaleCandy;
use App\Http\Requests\StoreSaleCandyRequest;
use App\Http\Requests\UpdateSaleCandyRequest;

class SaleCandyController extends Controller
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
     * @param  \App\Http\Requests\StoreSaleCandyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaleCandyRequest $request)
    {
        //
        return $request;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SaleCandy  $saleCandy
     * @return \Illuminate\Http\Response
     */
    public function show(SaleCandy $saleCandy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SaleCandy  $saleCandy
     * @return \Illuminate\Http\Response
     */
    public function edit(SaleCandy $saleCandy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaleCandyRequest  $request
     * @param  \App\Models\SaleCandy  $saleCandy
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaleCandyRequest $request, SaleCandy $saleCandy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SaleCandy  $saleCandy
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaleCandy $saleCandy)
    {
        //
    }
}
