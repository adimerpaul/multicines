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
        return Cui::all();
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
//        return date('Y-m-d H:i:s', strtotime(date('c')));;
        if (Cui::where('codigoPuntoVenta', $request->codigoPuntoVenta)->where('codigoSucursal', $request->codigoSucursal)->whereDate('fechaVigencia','>=', now())->count()>=1){
            return response()->json(['error' => 'El CUI ya existe'], 400);
        }else{
            $client = new \SoapClient("https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?WSDL",  [
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
            $result= $client->cuis([
                "SolicitudCuis"=>[
                    "codigoAmbiente"=>env('AMBIENTE'),
                    "codigoModalidad"=>env('MODALIDAD'),
                    "codigoPuntoVenta"=>$request->codigoPuntoVenta,
                    "codigoSistema"=>env('CODIGO_SISTEMA'),
                    "codigoSucursal"=>$request->codigoSucursal,
                    "nit"=>env('NIT'),
                ]
            ]);
            $cui = new Cui();
            $cui->codigo = $result->RespuestaCuis->codigo;
            $cui->fechaVigencia =  date('Y-m-d H:i:s', strtotime($result->RespuestaCuis->fechaVigencia));
            $cui->codigoPuntoVenta = $request->codigoPuntoVenta;
            $cui->codigoSucursal = $request->codigoSucursal;
            $cui->save();
            return response()->json(['success' => 'CUI creado correctamente'], 200);
        }




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