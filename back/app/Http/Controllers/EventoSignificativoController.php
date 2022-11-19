<?php

namespace App\Http\Controllers;

use App\Models\Cufd;
use App\Models\Cui;
use App\Models\EventoSignificativo;
use App\Http\Requests\StoreEventoSignificativoRequest;
use App\Http\Requests\UpdateEventoSignificativoRequest;
use App\Models\Sale;
use Illuminate\Http\Request;
use Phar;
use PharData;
class EventoSignificativoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return EventoSignificativo::orderBy('id','desc')->with('cufd')->get();
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
    public function validarPaquete(Request $request){
        try {
            $codigoPuntoVenta=0;
            $codigoSucursal=0;
            if (Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
                return response()->json(['message' => 'No existe CUI para la venta!!'], 400);
            }
            if (Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
                return response()->json(['message' => 'No exite CUFD para la venta!!'], 400);
            }
            $cui=Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();
            $cufd=Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();

            $client = new \SoapClient(env('URL_SIAT')."ServicioFacturacionCompraVenta?WSDL",  [
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


            $result= $client->validacionRecepcionPaqueteFactura([
                "SolicitudServicioValidacionRecepcionPaquete"=>[
//                "codigoAmbiente"=>env('AMBIENTE'),
//                "codigoMotivoEvento"=>$request->codigoMotivoEvento,
//                "codigoPuntoVenta"=>0,
//                "codigoSistema"=>env('CODIGO_SISTEMA'),
//                "codigoSucursal"=>0,
//                "cufd"=>$cufd->codigo,
//                "cufdEvento"=>$request->cufdEvento,
//                "cuis"=>$cui->codigo,
//                "descripcion"=>$request->descripcion,
//                "fechaHoraFinEvento"=>date('Y-m-d\TH:i:s.000', strtotime($request->fechaHoraFinEvento)),
//                "fechaHoraInicioEvento"=>date('Y-m-d\TH:i:s.000', strtotime($request->fechaHoraInicioEvento)),
//                "nit"=>env('NIT'),
                    "codigoAmbiente"=>env('AMBIENTE'),
                    "codigoDocumentoSector"=>"1",
                    "codigoEmision"=>2,
                    "codigoModalidad"=>1,
                    "codigoPuntoVenta"=>0,
                    "codigoSistema"=>env('CODIGO_SISTEMA'),
                    "codigoSucursal"=>0,
                    "cufd"=>$cufd->codigo,
                    "cuis"=>$cui->codigo,
                    "nit"=>env('NIT'),
                    "tipoFacturaDocumento"=>1,
                    "codigoRecepcion"=>$request->codigoRecepcion,
                ]
            ]);
            return  $result;
        }catch (\Exception $e) {
            return response()->json(["success"=>false,'message' => $e->getMessage()], 500);
        }
    }
    public function cantidadFacturas(Request $request){
        return Sale::where('siatEnviado',false)->where('fechaEmision','>=',$request->fechaInicio)->where('fechaEmision','<=',$request->fechaFin)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreEventoSignificativoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function recepcionPaqueteFactura(Request $request){
        $codigoPuntoVenta=$request->cufd['codigoPuntoVenta'];
        $codigoSucursal=$request->cufd['codigoSucursal'];
        if (Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No existe CUI para la venta!!'], 400);
        }
        if (Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No exite CUFD para la venta!!'], 400);
        }
        $cui=Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();
        $cufd=Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();

        $codigoAmbiente=env('AMBIENTE');
        $codigoDocumentoSector=1; // 1 compraventa 2 alquiler 23 prevaloradas
        $codigoEmision=2; // 1 online 2 offline 3 masivo
        $codigoModalidad=env('MODALIDAD'); //1 electronica 2 computarizada
        $codigoPuntoVenta=0;//
        $codigoSistema=env('CODIGO_SISTEMA');
        $tipoFacturaDocumento=1; // 1 con credito fiscal 2 sin creditofical 3 nota debito credito


        $a = new PharData('archivos/archive.tar');

        // ADD FILES TO archive.tar FILE

        foreach($request->datos as $file){
            $a->addFile("archivos/".$file['id'].".xml"); //Agregamos el fichero
        }

        // COMPRESS archive.tar FILE. COMPRESSED FILE WILL BE archive.tar.gz
        $a->compress(Phar::GZ);

        // NOTE THAT BOTH FILES WILL EXISTS. SO IF YOU WANT YOU CAN UNLINK archive.tar
        unlink('archivos/archive.tar');
        $firmar = new Firmar();
        $archivo=$firmar->getFileGzip("archivos/archive.tar.gz");
        $hashArchivo=hash('sha256', $archivo);
        unlink("archivos/archive.tar.gz");


        $client = new \SoapClient(env('URL_SIAT')."ServicioFacturacionCompraVenta?WSDL",  [
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


        $result= $client->recepcionPaqueteFactura([
            "SolicitudServicioRecepcionPaquete"=>[
//                "codigoAmbiente"=>env('AMBIENTE'),
//                "codigoMotivoEvento"=>$request->codigoMotivoEvento,
//                "codigoPuntoVenta"=>0,
//                "codigoSistema"=>env('CODIGO_SISTEMA'),
//                "codigoSucursal"=>0,
//                "cufd"=>$cufd->codigo,
//                "cufdEvento"=>$request->cufdEvento,
//                "cuis"=>$cui->codigo,
//                "descripcion"=>$request->descripcion,
//                "fechaHoraFinEvento"=>date('Y-m-d\TH:i:s.000', strtotime($request->fechaHoraFinEvento)),
//                "fechaHoraInicioEvento"=>date('Y-m-d\TH:i:s.000', strtotime($request->fechaHoraInicioEvento)),
//                "nit"=>env('NIT'),
                "codigoAmbiente"=>$codigoAmbiente,
                "codigoDocumentoSector"=>$codigoDocumentoSector,
                "codigoEmision"=>$codigoEmision,
                "codigoModalidad"=>$codigoModalidad,
                "codigoPuntoVenta"=>$codigoPuntoVenta,
                "codigoSistema"=>$codigoSistema,
                "codigoSucursal"=>$codigoSucursal,
                "cufd"=>$cufd->codigo,
                "cuis"=>$cui->codigo,
                "nit"=>env('NIT'),
                "tipoFacturaDocumento"=>$tipoFacturaDocumento,
                "archivo"=>$archivo,
                "fechaEnvio"=>date('Y-m-d\TH:i:s.000'),
                "hashArchivo"=>$hashArchivo,
                "cafc"=>"XXX",
                "cantidadFacturas"=>count($request->datos),
                "codigoEvento"=>$request->codigoRecepcionEventoSignificativo,

            ]
        ]);
        $eventoSignificativo=EventoSignificativo::find($request->id);
        $eventoSignificativo->codigoRecepcion=$result->RespuestaServicioFacturacion->codigoRecepcion;
        $eventoSignificativo->save();
        foreach($request->datos as $file){
            $sale=Sale::find($file['id']);
            $sale->codigoRecepcionEventoSignificativo=$request->codigoRecepcionEventoSignificativo;
            $sale->siatEnviado=1;
            $sale->save();
        }
        return var_dump($result);
    }
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
        try {
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
                $eventoSignificativo->fechaHoraFinEvento=$request->fechaHoraFinEvento;
                $eventoSignificativo->fechaHoraInicioEvento=$request->fechaHoraInicioEvento;
                $eventoSignificativo->codigoMotivoEvento=$request->codigoMotivoEvento;
                $eventoSignificativo->descripcion=$request->descripcion;
                $eventoSignificativo->cufd=$cufd->codigo;
                $eventoSignificativo->cufdEvento=$request->cufdEvento;
                $eventoSignificativo->cufd_id=$request->cufdEventoId;
                $eventoSignificativo->codigoRecepcionEventoSignificativo=$result->RespuestaListaEventos->codigoRecepcionEventoSignificativo;
                $eventoSignificativo->save();
                return response()->json(['message' => 'Evento Significativo registrado correctamente!!'], 200);
            }else{
                return response()->json(['message' => json_encode($result->RespuestaListaEventos->mensajesList) ], 500);
            }
        }catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
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
