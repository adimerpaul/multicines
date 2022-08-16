<?php

namespace App\Http\Controllers;

use App\Models\Cufd;
use App\Models\Cui;
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
        $codigoPuntoVenta=$request->codigoPuntoVenta;
        $codigoSucursal=$request->codigoSucursal;
        if (Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No existe CUI para la venta!!'], 400);
        }
        if (Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No exite CUFD para la venta!!'], 400);
        }
            $cui=Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();
            $cufd=Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();

            $client = new \SoapClient(env('URL_SIAT')."FacturacionOperaciones?WSDL",  [
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

            $result= $client->registroEventoSignificativo([
                "SolicitudEventoSignificativo"=>[
                    "codigoAmbiente"=>env('AMBIENTE'),
                    "codigoMotivoEvento"=>$request->codigoMotivoEvento,
                    "codigoPuntoVenta"=>0,
                    "codigoSistema"=>env('CODIGO_SISTEMA'),
                    "codigoSucursal"=>0,
                    "cufd"=>$cufd->codigo,
                    "cufdEvento"=>$request->cufdEvento,
                    "cuis"=>$cui->codigo,
                    "descripcion"=>$request->descripcion,
                    "fechaHoraFinEvento"=>date('Y-m-d\TH:i:s.000', strtotime($request->fechaHoraFinEvento)),
                    "fechaHoraInicioEvento"=>date('Y-m-d\TH:i:s.000', strtotime($request->fechaHoraInicioEvento)),
                    "nit"=>env('NIT'),
                ]
            ]);
//            $cufd = new Cufd();
//            $cufd->codigo = $result->RespuestaCufd->codigo;
//            $cufd->codigoControl = $result->RespuestaCufd->codigoControl;
//            $cufd->fechaVigencia =  date('Y-m-d H:i:s', strtotime($result->RespuestaCufd->fechaVigencia));
//            $cufd->fechaCreacion =  date('Y-m-d H:i:s');
//            $cufd->codigoPuntoVenta = $request->codigoPuntoVenta;
//            $cufd->codigoSucursal = $request->codigoSucursal;
//            $cufd->save();
if ($result->RespuestaListaEventos->transaccion){
    $eventoSignificativo = new EventoSignificativo();
    $eventoSignificativo->codigoPuntoVenta=$codigoPuntoVenta;
    $eventoSignificativo->codigoSucursal=$codigoSucursal;
    $eventoSignificativo->fechaHoraFinEvento=$request->fechaHoraInicioEvento;
    $eventoSignificativo->fechaHoraInicioEvento=$request->fechaHoraFinEvento;
    $eventoSignificativo->codigoMotivoEvento=$request->codigoMotivoEvento;
    $eventoSignificativo->descripcion=$request->descripcion;
    $eventoSignificativo->cufd=$cufd->codigo;
    $eventoSignificativo->cufdEvento=$request->cufdEvento;
    $eventoSignificativo->codigoRecepcionEventoSignificativo=$result->RespuestaListaEventos->codigoRecepcionEventoSignificativo;
    $eventoSignificativo->save();
    return response()->json(['message' => 'Evento Significativo registrado correctamente!!'], 200);
}else{
    return response()->json(['message' => 'Error al registrar el evento Significativo!!'], 400);
}

//            var_dump($result);
//            return $result->RespuestaListaEventos->transaccion;
//            return response()->json(['success' => 'Evento siginificativo creado correctamente'], 200);

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
