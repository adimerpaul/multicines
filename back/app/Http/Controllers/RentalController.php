<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Client;
use App\Models\Cufd;
use App\Models\Cui;
use App\Models\Detail;
use App\Models\Momentaneo;
use App\Models\Programa;
use App\Models\Rental;
use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\UpdateRentalRequest;
use App\Models\Sale;
use App\Models\Ticket;
use http\Client\Request;

class RentalController extends Controller
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
    public function rentalConsulta(Request $request)
    {
        $rental = Rental::where('fechaEmision','>=', $request->fechaInicio)
            ->where('fechaEmision','<=', $request->fechaFin)
            ->get();
        return response()->json($rental);
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
     * @param  \App\Http\Requests\StoreRentalRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSaleRequest $request)
    {
        if (Client::where('complemento',$request->client['complemento'])->where('numeroDocumento',$request->client['numeroDocumento'])->count()==1) {
            $client=Client::find($request->client['id']);
            $client->nombreRazonSocial=$request->client['nombreRazonSocial'];
            $client->codigoTipoDocumentoIdentidad=$request->client['codigoTipoDocumentoIdentidad'];
            $client->save();
        }else if(Client::where('numeroDocumento',$request->client['numeroDocumento'])->count()==1){
            $client=Client::find($request->client['id']);
            $client->nombreRazonSocial=$request->client['nombreRazonSocial'];
            $client->codigoTipoDocumentoIdentidad=$request->client['codigoTipoDocumentoIdentidad'];
            $client->save();
        }else{
            $client=new Client();
            $client->nombreRazonSocial=$request->client['nombreRazonSocial'];
            $client->codigoTipoDocumentoIdentidad=$request->client['codigoTipoDocumentoIdentidad'];
            $client->numeroDocumento=$request->client['numeroDocumento'];
            $client->complemento=$request->client['complemento'];
            $client->save();
        }

        $codigoAmbiente=env('AMBIENTE');
        $codigoDocumentoSector=2; // 1 compraventa 2 alquiler 23 prevaloradas
        $codigoEmision=1; // 1 online 2 offline 3 masivo
        $codigoModalidad=env('MODALIDAD'); //1 electronica 2 computarizada
        $codigoPuntoVenta=0;
        $codigoSistema=env('CODIGO_SISTEMA');
        $tipoFacturaDocumento=1; // 1 con credito fiscal 2 sin creditofical 3 nota debito credito

        $codigoSucursal=0;

        $user=(object)["name"=>"admin","id"=>1];

        if (Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No existe CUI para la venta!!'], 400);
        }
        if (Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No exite CUFD para la venta!!'], 400);
        }
        $cui=Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();
        $cufd=Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();
//        if (Sale::where('cui', $cui->codigo)->count()==0){
//            $numeroFactura=1;
//        }else{
//            $sale=Sale::where('cui',$cui->codigo)->orderBy('numeroFactura', 'desc')->first();
//            $numeroFactura=intval($sale->numeroFactura)+1;
//        }

        if (Sale::where('cufd', $cufd->codigo)->count()==0){
            $numeroFactura=1;
        }else{
            $sale=Sale::where('cufd',$cufd->codigo)->orderBy('numeroFactura', 'desc')->first();
            $numeroFactura=intval($sale->numeroFactura)+1;
        }

        $sale=new Rental();
//        $sale->numeroFactura=$numeroFactura;
//        $sale->cuf="";
//        $sale->cufd=$cufd->codigo;
//        $sale->cui=$cui->codigo;
//        $sale->codigoSucursal=$codigoSucursal;
//        $sale->codigoPuntoVenta=$codigoPuntoVenta;
//        $sale->fechaEmision=now();
//        $sale->montoTotal=$request->montoTotal;
//        $sale->usuario=$user->name;
//        $sale->codigoDocumentoSector=$codigoDocumentoSector;
//        $sale->user_id=$user->id;
//        $sale->cufd_id=$cufd->id;
//        $sale->client_id=$request->client->id;
        $sale->numeroFactura=$numeroFactura;
        $sale->cuf="";
        $sale->cufd=$cufd->codigo;
        $sale->cui=$cui->codigo;
        $sale->codigoSucursal=$codigoSucursal;
        $sale->codigoPuntoVenta=$codigoPuntoVenta;
        $sale->fechaEmision=now();
        $sale->montoTotal=$request->montoTotal;
        $sale->usuario=$user->name;
        $sale->periodoFacturado=$request->periodoFacturado;
        $sale->codigoDocumentoSector=$codigoDocumentoSector;
        $sale->actividadEconomica=681011;
        $sale->codigoProductoSin=72111;
        $sale->codigoProducto="10101";
        $sale->descripcion=$request->periodoFacturado;
        $sale->cantidad=1;
        $sale->unidadMedida=1;
        $sale->precioUnitario=$request->montoTotal;
        $sale->montoDescuento=0;
        $sale->subTotal=$request->montoTotal;
        $sale->user_id=$user->id;
        $sale->cufd_id=$cufd->id;
        $sale->client_id=$client->id;
        $sale->save();


        $cuf = new CUF();
        //     * @param nit NIT emisor
//     * @param fh Fecha y Hora en formato yyyyMMddHHmmssSSS
//     * @param sucursal
//     * @param mod Modalidad
//     * @param temision Tipo de Emision
//     * @param cdf Codigo Documento Fiscal
//     * @param tds Tipo Documento Sector
//     * @param nf Numero de Factura
//     * @param pos Punto de Venta
        $fechaEnvio=date("Y-m-d\TH:i:s.000");
        $cuf = $cuf->obtenerCUF(env('NIT'), date("YmdHis000"), $codigoSucursal, $codigoModalidad, $codigoEmision, $tipoFacturaDocumento, $codigoDocumentoSector, $numeroFactura, $codigoPuntoVenta);
        $cuf=$cuf.$cufd->codigoControl;
        $text="<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<facturaElectronicaAlquilerBienInmueble xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
                                        xsi:noNamespaceSchemaLocation='facturaElectronicaAlquilerBienInmueble.xsd'>
    <cabecera>
        <nitEmisor>".env('NIT')."</nitEmisor>
        <razonSocialEmisor>".env('RAZON')."</razonSocialEmisor>
        <municipio>Oruro</municipio>
        <telefono>".env('TELEFONO')."</telefono>
        <numeroFactura>1</numeroFactura>
        <cuf>$cuf</cuf>
        <cufd>".$cufd->codigo."</cufd>
        <codigoSucursal>$codigoSucursal</codigoSucursal>
        <direccion>".env('DIRECCION')."</direccion>
        <codigoPuntoVenta>$codigoPuntoVenta</codigoPuntoVenta>
        <fechaEmision>$fechaEnvio</fechaEmision>
        <nombreRazonSocial>".$client->nombreRazonSocial."</nombreRazonSocial>
        <codigoTipoDocumentoIdentidad>".$client->codigoTipoDocumentoIdentidad."</codigoTipoDocumentoIdentidad>
        <numeroDocumento>".$client->numeroDocumento."</numeroDocumento>
        <complemento>".$client->complemento."</complemento>
        <codigoCliente>".$client->id."</codigoCliente>
        <periodoFacturado>."$request->periodoFacturado".</periodoFacturado>
        <codigoMetodoPago>1</codigoMetodoPago>
        <numeroTarjeta xsi:nil='true'/>
        <montoTotal>".$request->montoTotal."</montoTotal>
        <montoTotalSujetoIva>".$request->montoTotal."</montoTotalSujetoIva>
        <codigoMoneda>1</codigoMoneda>
        <tipoCambio>1</tipoCambio>
        <montoTotalMoneda>100</montoTotalMoneda>
        <descuentoAdicional xsi:nil='true'/>
        <codigoExcepcion>0</codigoExcepcion>
        <cafc xsi:nil='true'/>
        <leyenda>Una leyenda</leyenda>
        <usuario>vjcm</usuario>
        <codigoDocumentoSector>2</codigoDocumentoSector>
    </cabecera>
    <detalle>
        <actividadEconomica>451010</actividadEconomica>
        <codigoProductoSin>49111</codigoProductoSin>
        <codigoProducto>123</codigoProducto>
        <descripcion>Alquiler mes de Febrero</descripcion>
        <cantidad>1</cantidad>
        <unidadMedida>1</unidadMedida>
        <precioUnitario>100</precioUnitario>
        <montoDescuento>0</montoDescuento>
        <subTotal>100</subTotal>
    </detalle>
</facturaElectronicaAlquilerBienInmueble>";

        $xml = new SimpleXMLElement($text);
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $nameFile=$sale->id;
        $dom->save("archivos/".$nameFile.'.xml');

        $firmar = new Firmar();
        $firmar->firmar("archivos/".$nameFile.'.xml');


        $xml = new DOMDocument();
        $xml->load("archivos/".$nameFile.'.xml');
        if (!$xml->schemaValidate('./facturaElectronicaCompraVenta.xsd')) {
            echo "invalid";
        }
        else {
//            echo "validated";
        }
//        exit;


        $file = "archivos/".$nameFile.'.xml';
        $gzfile = "archivos/".$nameFile.'.xml'.'.gz';
        $fp = gzopen ($gzfile, 'w9');
        gzwrite ($fp, file_get_contents($file));
        gzclose($fp);
//        unlink($file);


        $archivo=$firmar->getFileGzip("archivos/".$nameFile.'.xml'.'.gz');
        $hashArchivo=hash('sha256', $archivo);
        unlink($gzfile);
//        return $request;
//        exit;




        try {
            $client = new \SoapClient("https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?WSDL",  [
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
            $result= $client->recepcionFactura([
                "SolicitudServicioRecepcionFactura"=>[
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
                    "fechaEnvio"=>$fechaEnvio,
                    "hashArchivo"=>$hashArchivo,
                ]
            ]);
            $sale->siatEnviado=true;
            $sale->codigoRecepcion=$result->RespuestaServicioFacturacion->codigoRecepcion;
        }catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()]);
            $sale->siatEnviado=false;
        }

//        return $result;
//        if (isset($result->RespuestaServicioFacturacion)){
//
//        }else{
//
//        }

        $sale->cuf=$cuf;

//
        $sale->save();
        return response()->json(['sale' => $sale->with('details'),"tickets"=>$data]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Rental  $rental
     * @return \Illuminate\Http\Response
     */
    public function show(Rental $rental)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Rental  $rental
     * @return \Illuminate\Http\Response
     */
    public function edit(Rental $rental)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRentalRequest  $request
     * @param  \App\Models\Rental  $rental
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRentalRequest $request, Rental $rental)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rental  $rental
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rental $rental)
    {
        //
    }
}