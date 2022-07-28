<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Cui;
use App\Models\Sector;

class ActivityController extends Controller
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
     * @param  \App\Http\Requests\StoreActivityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreActivityRequest $request)
    {

        $cui=Cui::where('codigoPuntoVenta', $request->codigoPuntoVenta)->where('codigoSucursal', $request->codigoSucursal)->whereDate('fechaVigencia','>=', now());
        if ($cui->count()==0){
            return response()->json(['message' => 'El CUI no existe'], 400);
        }
        Activity::where('codigoPuntoVenta', $request->codigoPuntoVenta)->where('codigoSucursal', $request->codigoSucursal)->delete();
        $client = new \SoapClient("https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?WSDL",  [
            'stream_context' => stream_context_create([
                'http' => [
                    'header' => "apikey: TokenApi ".env('TOKEN'),
                ]
            ]),
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            'trace' => 1,
            'use' => SOAP_LITERAL,
            'style' => SOAP_DOCUMENT,
        ]);
        $result= $client->sincronizarActividades([
            "SolicitudSincronizacion"=>[
                "codigoAmbiente"=>env('AMBIENTE'),
                "codigoPuntoVenta"=>$request->codigoPuntoVenta,
                "codigoSistema"=>env('CODIGO_SISTEMA'),
                "codigoSucursal"=>$request->codigoSucursal,
                "cuis"=>$cui->first()->codigo,
                "nit"=>env('NIT'),
            ]
        ]);

        Sector::where('codigoPuntoVenta', $request->codigoPuntoVenta)->where('codigoSucursal', $request->codigoSucursal)->delete();
        foreach ($result->RespuestaListaActividades->listaActividades as $actividad) {
            $activity = new Activity();
            $activity->codigoCaeb = $actividad->codigoCaeb;
            $activity->descripcion = $actividad->descripcion;
            $activity->tipoActividad = $actividad->tipoActividad;
            $activity->codigoPuntoVenta = $request->codigoPuntoVenta;
            $activity->codigoSucursal = $request->codigoSucursal;
            $activity->save();
        }
        $result= $client->sincronizarListaActividadesDocumentoSector([
            "SolicitudSincronizacion"=>[
                "codigoAmbiente"=>env('AMBIENTE'),
                "codigoPuntoVenta"=>$request->codigoPuntoVenta,
                "codigoSistema"=>env('CODIGO_SISTEMA'),
                "codigoSucursal"=>$request->codigoSucursal,
                "cuis"=>$cui->first()->codigo,
                "nit"=>env('NIT'),
            ]
        ]);
        foreach ($result->RespuestaListaActividadesDocumentoSector->listaActividadesDocumentoSector as $registro) {
            $sector = new Sector();
            $sector->codigoActividad=$registro->codigoActividad;
            $sector->codigoDocumentoSector=$registro->codigoDocumentoSector;
            $sector->tipoDocumentoSector=$registro->tipoDocumentoSector;
            $sector->codigoPuntoVenta = $request->codigoPuntoVenta;
            $sector->codigoSucursal = $request->codigoSucursal;
            $sector->save();
        }
        return response()->json(['success' => 'Actividades sincronizadas'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function edit(Activity $activity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateActivityRequest  $request
     * @param  \App\Models\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateActivityRequest $request, Activity $activity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Activity  $activity
     * @return \Illuminate\Http\Response
     */
    public function destroy(Activity $activity)
    {
        //
    }
}
