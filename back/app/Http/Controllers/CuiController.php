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
        // Incluye los dados de baja para mostrarlos en el historial
        return Cui::withTrashed()->with('userBaja:id,name')->orderBy('id','desc')->get();
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
        if (Cui::where('codigoPuntoVenta', $request->codigoPuntoVenta)->where('codigoSucursal', $request->codigoSucursal)->where('fechaVigencia','>=', now())->count()>=1){
            return response()->json(['message' => 'El CUI ya existe'], 400);
        }else{
            $client = new \SoapClient(env("URL_SIAT")."FacturacionCodigos?WSDL",  [
                'stream_context' => stream_context_create([
                    'http' => [
                        'header' => "apikey: TokenApi ".\App\Models\SiatToken::actual(),
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
            $respuesta = $result->RespuestaCuis;
            $mensajes = [];
            if (isset($respuesta->mensajesList)) {
                $lista = is_array($respuesta->mensajesList) ? $respuesta->mensajesList : [$respuesta->mensajesList];
                foreach ($lista as $m) {
                    $mensajes[] = $m->descripcion ?? '';
                }
            }
            if (empty($respuesta->codigo)) {
                return response()->json(['message' => 'El SIAT no devolvió un CUIS: ' . (implode(' / ', $mensajes) ?: 'sin detalle')], 400);
            }
            $cui = new Cui();
            $cui->codigo = $respuesta->codigo;
            $cui->fechaVigencia =  date('Y-m-d H:i:s', strtotime($result->RespuestaCuis->fechaVigencia));
            $cui->codigoPuntoVenta = $request->codigoPuntoVenta;
            $cui->codigoSucursal = $request->codigoSucursal;
            $cui->fechaCreacion= date('Y-m-d H:i:s');
            $cui->save();
            return response()->json([
                'success' => 'CUI creado correctamente',
                'codigo' => $cui->codigo,
                'mensaje' => implode(' / ', array_filter($mensajes)),
            ], 200);
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
    /**
     * Dar de baja un CUIS: deja de usarse para facturar (soft delete, ya que todas las
     * consultas de CUIS excluyen los eliminados) pero queda en el historial con el motivo.
     */
    public function destroy(\Illuminate\Http\Request $request, Cui $cui)
    {
        abort_unless(
            $request->user()->permisos()->where('permisos.id', 2)->exists(),
            403,
            'No tiene permiso para dar de baja el CUIS'
        );
        $request->validate(['motivo' => 'required|string|max:255']);

        $cui->motivoBaja = $request->motivo;
        $cui->userBaja_id = $request->user()->id;
        $cui->save();
        $cui->delete();

        return response()->json(['message' => 'CUIS dado de baja']);
    }
}
