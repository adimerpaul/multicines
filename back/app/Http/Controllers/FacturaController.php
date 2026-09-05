<?php

namespace App\Http\Controllers;

use App\Models\Cufd;
use App\Models\Cui;
use App\Models\Factura;
use App\Http\Requests\StoreFacturaRequest;
use App\Http\Requests\UpdateFacturaRequest;
use Illuminate\Http\Request;
class FacturaController extends Controller{
    public function buscarFacturas(Request $request){ return $this->getYearMonthFacturas($request); }
    public function index(){ return Factura::all(); }
    public function show(Factura $factura){ return $factura; }
    public function store(StoreFacturaRequest $request){ return Factura::create($request->all()); }
    public function update(UpdateFacturaRequest $request, Factura $factura){ return $factura->update($request->all()); }
    public function destroy(Factura $factura){ return $factura->delete(); }

    public function getYearMonthFacturas(Request $request)
    {
        $data = $request->validate([
            'anio' => 'required|integer|between:2000,2100',
            'mes' => 'required|integer|between:1,12',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|between:1,100',
            'filter' => 'nullable|string|max:200',
        ]);
        $start = \Carbon\Carbon::create($data['anio'], $data['mes'], 1)->startOfDay();
        $query = Factura::where('fecha', '>=', $start->toDateString())
            ->where('fecha', '<', $start->copy()->addMonth()->toDateString());
        if (!empty($data['filter'])) {
            $query->where(function ($query) use ($data) {
                foreach (['nFactura', 'cuf', 'nit', 'nombre', 'estado'] as $column) {
                    $query->orWhere($column, 'like', '%'.$data['filter'].'%');
                }
            });
        }
        return $query->orderByDesc('fecha')->orderByDesc('id')->paginate($data['per_page'] ?? 50);
    }

    public function conciliacion(Request $request)
    {
        $data = $request->validate([
            'anio' => 'required|integer|between:2000,2100', 'mes' => 'required|integer|between:1,12',
            'page' => 'nullable|integer|min:1', 'per_page' => 'nullable|integer|between:1,100',
            'filter' => 'nullable|string|max:200', 'origen' => 'nullable|in:BOLETERIA,CANDY,ALQUILER,PARQUEO',
            'vinculo' => 'nullable|in:vinculada,solo_siat,falta_siat',
            'diferencia' => 'nullable|in:diferenciaMonto,diferenciaEstado,diferenciaFecha,duplicado',
            'anuladas' => 'nullable|boolean',
        ]);
        return response()->json(app(\App\Services\FacturaReconciliation::class)->report($data));
    }
    public function import(Request $request)
    {
        $request->validate(['archivo' => 'required|file|max:20480']);
        $file = $request->file('archivo');
        $lock = \Illuminate\Support\Facades\Cache::lock('facturas-import', 300);
        if (!$lock->get()) {
            return response()->json(['message' => 'Ya hay una importación en curso. Intente nuevamente al terminar.'], 409);
        }
        try {
            set_time_limit(240);
            return response()->json(app(\App\Services\FacturaFileImport::class)->import(
                $file->getRealPath(), $file->getClientOriginalExtension()
            ));
        } finally {
            $lock->release();
        }
    }
    public function anularMasivo(){
        $facturas=Factura::where('impuesto', '=', 'NO')->get();

//        return $request->all();
        $codigoAmbiente=env('AMBIENTE');
        $codigoDocumentoSector=1; // 1 compraventa 2 alquiler 23 prevaloradas
        $codigoEmision=1; // 1 online 2 offline 3 masivo
        $codigoModalidad=env('MODALIDAD'); //1 electronica 2 computarizada
        $codigoPuntoVenta=0;
        $codigoSistema=env('CODIGO_SISTEMA');
        $tipoFacturaDocumento=1; // 1 con credito fiscal 2 sin creditofical 3 nota debito credito
        $codigoSucursal=0;
        $nit=ENV('NIT');

        if (Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No existe CUI para la venta!!'], 400);
        }
        if (Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No exite CUFD para la venta!!'], 400);
        }
        $cui=Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();
        $cufd=Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();

        //codigomotivo
        //cuf

        try {
            foreach ($facturas  as $factura){
                $client = new \SoapClient(env("URL_SIAT")."ServicioFacturacionCompraVenta?WSDL",  [
                    'stream_context' => stream_context_create([
                        'http' => [
                            'header' => "apikey: TokenApi " . env('TOKEN'),
                        ]
                    ]),
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
                    'trace' => 1,
                    'use' => SOAP_LITERAL,
                    'style' => SOAP_DOCUMENT,
                ]);
                $result= $client->anulacionFactura([
                    "SolicitudServicioAnulacionFactura"=>[
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
                        "codigoMotivo"=>1,
                        "cuf"=>$factura->cuf,
                    ]
                ]);
                error_log("result".json_encode($result));
            }

        }catch (\Exception $e) {
            //return response()->json(['error' => $e->getMessage()]);
            return response()->json(['message' => 'anulado error'], 400);
        }
    }

}
