<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Cufd;
use App\Models\Cui;
use App\Models\Detail;
use App\Models\Momentaneo;
use App\Models\Sale;
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
        //return $request;
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
        $codigoDocumentoSector=1; // 1 compraventa 2 alquiler 23 prevaloradas
        $codigoEmision=1; // 1 online 2 offline 3 masivo
        $codigoModalidad=env('MODALIDAD'); //1 electronica 2 computarizada
        $codigoPuntoVenta=1;
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
        if (SaleCandy::where('cui', $cui->codigo)->count()==0){
            $numeroFactura=1;
        }else{
            $sale=SaleCandy::where('cui',$cui->codigo)->orderBy('numeroFactura', 'desc')->first();
            $numeroFactura=$sale->numeroFactura+1;
        }

        $sale=new SaleCandy();
        $sale->numeroFactura=$numeroFactura;
        $sale->cuf="";
        $sale->cufd=$cufd->codigo;
        $sale->cui=$cui->codigo;
        $sale->codigoSucursal=$codigoSucursal;
        $sale->codigoPuntoVenta=$codigoPuntoVenta;
        $sale->fechaEmision=now();
        $sale->montoTotal=$request->montoTotal;
        $sale->usuario=$user->name;
        $sale->codigoDocumentoSector=$codigoDocumentoSector;
        $sale->user_id=$user->id;
        $sale->client_id=$client->id;
        $sale->save();

        $data=[];
        $dataDetail=[];
        $detalleFactura="";

        foreach ($request->detalleVenta as $detalle){
            $detalleFactura.="<detalle>
                <actividadEconomica>472000</actividadEconomica>
                <codigoProductoSin>99100</codigoProductoSin>
                <codigoProducto>".$detalle['product_id']."</codigoProducto>
                <descripcion>".$detalle['nommbre']."</descripcion>
                <cantidad>".$detalle['cantidad']."</cantidad>
                <unidadMedida>62</unidadMedida>
                <precioUnitario>".$detalle['precio']."</precioUnitario>
                <montoDescuento>0</montoDescuento>
                <subTotal>".$detalle['subtotal']."</subTotal>
                <numeroSerie xsi:nil='true'/>
                <numeroImei xsi:nil='true'/>
            </detalle>";
            $d=[
                'actividadEconomica'=>"472000",
                'codigoProductoSin'=>"99100",
                'cantidad'=>$detalle['cantidad'],
                'precioUnitario'=>$detalle['precio'],
                'subTotal'=>$detalle['subtotal'],
                'sale_id'=>$sale->id,
                'programa_id'=>$detalle['product_id'],
                'descripcion'=>$detalle['nombre'],
            ];
            array_push($dataDetail, $d);
        }
        //
        Detail::insert($dataDetail);


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
<facturaElectronicaCompraVenta xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:noNamespaceSchemaLocation='facturaElectronicaCompraVenta.xsd'>    <cabecera>
        <nitEmisor>".env('NIT')."</nitEmisor>
        <razonSocialEmisor>".env('RAZON')."</razonSocialEmisor>
        <municipio>Oruro</municipio>
        <telefono>2846005</telefono>
        <numeroFactura>$numeroFactura</numeroFactura>
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
        <codigoMetodoPago>1</codigoMetodoPago>
        <numeroTarjeta xsi:nil='true'/>
        <montoTotal>".$sale->montoTotal."</montoTotal>
        <montoTotalSujetoIva>".$sale->montoTotal."</montoTotalSujetoIva>
        <codigoMoneda>1</codigoMoneda>
        <tipoCambio>1</tipoCambio>
        <montoTotalMoneda>".$sale->montoTotal."</montoTotalMoneda>
        <montoGiftCard xsi:nil='true'/>
        <descuentoAdicional>0</descuentoAdicional>
        <codigoExcepcion xsi:nil='true'/>
        <cafc xsi:nil='true'/>
        <leyenda>Ley N° 453: Tienes derecho a un trato equitativo sin discriminación en la oferta de servicios.</leyenda>
        <usuario>".explode(" ", $user->name)[0]."</usuario>
        <codigoDocumentoSector>".$codigoDocumentoSector."</codigoDocumentoSector>
        </cabecera>";

        $text.=$detalleFactura;
        $text.="</facturaElectronicaCompraVenta>";

        $xml = new SimpleXMLElement($text);
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $nameFile=microtime();
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
        unlink($file);


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
