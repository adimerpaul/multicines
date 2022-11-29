<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\Client;
use App\Models\Cortesia;
use App\Models\Cufd;
use App\Models\Cui;
use App\Models\Detail;
use App\Models\Leyenda;
use App\Models\Momentaneo;
use App\Models\Programa;
use App\Models\Sale;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Ticket;
//require 'CUF.php';
//use CUF;
use App\Models\User;
use App\Patrones\Env;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use SimpleXMLElement;
use DOMDocument;

class SaleController extends Controller
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
    }
    public function movies(Request $request)
    {
        //return Programa::select('movie_id')->groupBy('movie_id')->with('movie')->whereDate('fecha',$request->fecha)->get();
//        return DB::SELECT("select m.id,m.nombre,m.duracion,m.formato,m.imagen,count(*) as cantidad from programas p,movies m,tickets t where p.movie_id=m.id and t.programa_id=p.id and p.fecha='$request->fecha' GROUP by m.id,m.nombre,m.duracion,m.formato,m.imagen");
        return DB::select("
        select m.id,m.nombre,m.duracion,m.formato,m.imagen,(
        SELECT count(*) FROM tickets WHERE programa_id=p.id and devuelto=0
        ) as cantidad
        from programas p
        INNER JOIN movies m ON p.movie_id=m.id
        where p.fecha='$request->fecha'
        and p.activo='ACTIVO'
        GROUP by m.id,m.nombre,m.duracion,m.formato,m.imagen;");
    }

    public function movietotal(Request $request){
        return DB::SELECT("SELECT p.movie_id,COUNT(*) from tickets t inner JOIN programas p on t.programa_id=p.id where t.fechaFuncion='$request->fecha' GROUP by p.movie_id");
    }
    public function eventSearch(Request $request)
    {
        return Sale::where('siatEnviado',false)->count();
    }
    public function hours(Request $request)
    {
        return Programa::with('sala')->with('price')->with('movie')->whereDate('fecha',$request->fecha)->where('movie_id',$request->id)->get();
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSaleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function mySeats(Request $request){
        $seats=DB::select("SELECT a.fila,a.columna,a.letra,(
            IF(a.activo='ACTIVO',
               IF((SELECT COUNT(*) FROM tickets t WHERE t.programa_id=p.id AND t.fila=a.fila AND t.columna=a.columna AND t.letra=a.letra and t.devuelto=0)=1, 'OCUPADO',
                 IF((SELECT COUNT(*) FROM momentaneos m WHERE m.programa_id=p.id AND m.fila=a.fila AND m.columna=a.columna AND m.letra=a.letra)=1,'RESERVADO','LIBRE'))
               , 'INACTIVO')
            ) activo
            FROM programas p
            INNER JOIN salas s ON s.id=p.sala_id
            INNER JOIN seats a ON a.sala_id=s.id
            WHERE p.id=".$request->id.";");
        return $seats;
    }

    public function disponibleSeats(Request $request){
        return DB::SELECT("Select (select count(*) from tickets t where t.programa_id=$request->id and t.devuelto=0) as venta,
       (selecT COUNT(*) from momentaneos m where m.programa_id=$request->id) as temp,
       (select count(*) from tickets t where t.programa_id=$request->id and t.devuelto=1) as dev,
       (SELECT s.capacidad from salas s, programas p where s.id=p.sala_id and p.id=$request->id) as salatotal;");
    }

    public function store(StoreSaleRequest $request)
    {
        if ($request->client['complemento']==null){
            $complemento="";
        }else{
            $complemento=$request->client['complemento'];
        }
        if ( $complemento!= "" && Client::whereComplemento($complemento)->where('numeroDocumento',$request->client['numeroDocumento'])->count()==1) {
            $client=Client::find($request->client['id']);
            $client->nombreRazonSocial=strtoupper($request->client['nombreRazonSocial']);
            $client->codigoTipoDocumentoIdentidad=$request->client['codigoTipoDocumentoIdentidad'];
            $client->email=$request->client['email'];
            $client->save();
//            return "actualizado con complento";
        }else if(Client::where('numeroDocumento',$request->client['numeroDocumento'])->whereComplemento($complemento)->count()){
            $client=Client::find($request->client['id']);
            $client->nombreRazonSocial=strtoupper($request->client['nombreRazonSocial']);
            $client->codigoTipoDocumentoIdentidad=$request->client['codigoTipoDocumentoIdentidad'];
            $client->email=$request->client['email'];
            $client->save();
//            return "actualizado";
        }else{
            $client=new Client();
            $client->nombreRazonSocial=strtoupper($request->client['nombreRazonSocial']);
            $client->codigoTipoDocumentoIdentidad=$request->client['codigoTipoDocumentoIdentidad'];
            $client->numeroDocumento=$request->client['numeroDocumento'];
            $client->complemento=strtoupper($request->client['complemento']);
            $client->email=$request->client['email'];
            $client->save();
//            return "nuevo";
        }

        if ($request->client['numeroDocumento']=="0"){
            return $this->insertarRecibo($request,$client);
        }
        if ($request->vip=="SI"){
            return $this->insertarVip($request,$client);
        }

        if ($request->cortesia=="SI"){
            return $this->insertarCortesia($request,$client);
        }


        $codigoAmbiente=env('AMBIENTE');
        $codigoDocumentoSector=1; // 1 compraventa 2 alquiler 23 prevaloradas
        $codigoEmision=1; // 1 online 2 offline 3 masivo
        $codigoModalidad=env('MODALIDAD'); //1 electronica 2 computarizada
        $codigoPuntoVenta=0;
        $codigoSistema=env('CODIGO_SISTEMA');
        $tipoFacturaDocumento=1; // 1 con credito fiscal 2 sin creditofical 3 nota debito credito

        $codigoSucursal=0;

        $user=User::find($request->user()->id);

        if (Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No existe CUI para la venta!!'], 400);
        }
        if (Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No exite CUFD para la venta!!'], 400);
        }
        $cui=Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();
        $cufd=Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->where('fechaVigencia','>=', now())->first();


        if (Sale::where('cufd', $cufd->codigo)->where('tipo','BOLETERIA')->count()==0){
            $numeroFactura=1;
        }else{
//            $sale=Sale::where('cufd',$cufd->codigo)->where('tipo','BOLETERIA')->orderBy('numeroFactura', 'desc')->first();
            $max=Sale::where('cufd',$cufd->codigo)->where('tipo','BOLETERIA')->max('numeroFactura');
            $numeroFactura=intval($max)+1;
        }
        if (count(Sale::all())==0) {
            $saleId=1;
        }else{
            $sale=Sale::orderBy('id', 'desc')->first();
            $saleId=intval($sale->id)+1;
        }

        $detalleFactura="";
        foreach ($request->detalleVenta as $detalle){
            $detalleFactura.="<detalle>
                <actividadEconomica>590000</actividadEconomica>
                <codigoProductoSin>99100</codigoProductoSin>
                <codigoProducto>".$detalle['programa_id']."</codigoProducto>
                <descripcion>".$detalle['pelicula']."</descripcion>
                <cantidad>".$detalle['cantidad']."</cantidad>
                <unidadMedida>62</unidadMedida>
                <precioUnitario>".$detalle['precio']."</precioUnitario>
                <montoDescuento>0</montoDescuento>
                <subTotal>".$detalle['subtotal']."</subTotal>
                <numeroSerie xsi:nil='true'/>
                <numeroImei xsi:nil='true'/>
            </detalle>";
        }
//
//        Ticket::insert($data);
//
//        Detail::insert($dataDetail);


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
        $leyenda=Leyenda::where("codigoActividad","590000")->get();
        $count=$leyenda->count();
        $leyenda=$leyenda[rand(0,$count-1)]->descripcionLeyenda;

        $fechaEnvio=date("Y-m-d\TH:i:s.000");

        $cuf = $cuf->obtenerCUF(env('NIT'), date("YmdHis000"), $codigoSucursal, $codigoModalidad, $codigoEmision, $tipoFacturaDocumento, $codigoDocumentoSector, $numeroFactura, $codigoPuntoVenta);
        $cuf=$cuf.$cufd->codigoControl;
        $text="<?xml version='1.0' encoding='UTF-8' standalone='yes'?>
<facturaElectronicaCompraVenta xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:noNamespaceSchemaLocation='facturaElectronicaCompraVenta.xsd'>    <cabecera>
        <nitEmisor>".env('NIT')."</nitEmisor>
        <razonSocialEmisor>".env('RAZON')."</razonSocialEmisor>
        <municipio>Oruro</municipio>
        <telefono>".env('TELEFONO')."</telefono>
        <numeroFactura>$numeroFactura</numeroFactura>
        <cuf>$cuf</cuf>
        <cufd>".$cufd->codigo."</cufd>
        <codigoSucursal>$codigoSucursal</codigoSucursal>
        <direccion>".env('DIRECCION')."</direccion>
        <codigoPuntoVenta>$codigoPuntoVenta</codigoPuntoVenta>
        <fechaEmision>$fechaEnvio</fechaEmision>
        <nombreRazonSocial>".str_replace("&","&amp;",$client->nombreRazonSocial)."</nombreRazonSocial>
        <codigoTipoDocumentoIdentidad>".$client->codigoTipoDocumentoIdentidad."</codigoTipoDocumentoIdentidad>
        <numeroDocumento>".$client->numeroDocumento."</numeroDocumento>
        <complemento>".$client->complemento."</complemento>
        <codigoCliente>".$client->id."</codigoCliente>
        <codigoMetodoPago>1</codigoMetodoPago>
        <numeroTarjeta xsi:nil='true'/>
        <montoTotal>".$request->montoTotal."</montoTotal>
        <montoTotalSujetoIva>".$request->montoTotal."</montoTotalSujetoIva>
        <codigoMoneda>1</codigoMoneda>
        <tipoCambio>1</tipoCambio>
        <montoTotalMoneda>".$request->montoTotal."</montoTotalMoneda>
        <montoGiftCard xsi:nil='true'/>
        <descuentoAdicional>0</descuentoAdicional>
        <codigoExcepcion>".($client->codigoTipoDocumentoIdentidad==5?1:0)."</codigoExcepcion>
        <cafc xsi:nil='true'/>
        <leyenda>$leyenda</leyenda>
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
        $nameFile=$saleId;
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

        $archivo=$firmar->getFileGzip("archivos/".$nameFile.'.xml'.'.gz');
        $hashArchivo=hash('sha256', $archivo);
        unlink($gzfile);
        try {
            $clientSoap = new \SoapClient(env("URL_SIAT")."ServicioFacturacionCompraVenta?WSDL",  [
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
            $result= $clientSoap->recepcionFactura([
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



            if ($result->RespuestaServicioFacturacion->transaccion) {
                $sale=new Sale();
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
                $sale->cufd_id=$cufd->id;
                $sale->client_id=$client->id;
                $sale->leyenda=$leyenda;
                $sale->vip=$request->vip;
                $sale->credito=$request->tarjeta;
                $sale->save();

                if ($request->client['email']!==''){
                    $details=[
                        "title"=>"Factura",
                        "body"=>"Gracias por su compra",
                        "online"=>true,
                        "anulado"=>false,
                        "cuf"=>"",
                        "numeroFactura"=>"",
                        "sale_id"=>$sale->id,
                        "carpeta"=>"archivos",
                    ];
                    Mail::to($request->client['email'])->send(new TestMail($details));
                }
                $momentaneos=Momentaneo::where('user_id',$user->id)->get();
                $data=[];
                $dataDetail=[];
                foreach ($momentaneos as $m){
                    $programa=Programa::find($m->programa_id);
                    $numBoleto=Ticket::where('programa_id',$m->programa_id)->count()+1;
                    if(Ticket::where('programa_id',$m->programa_id)
                            ->where("fila",$m->fila)
                            ->where("devuelto",0)
                            ->where("columna",$m->columna)
                            ->where("letra",$m->letra)->where("sala_id",$programa->sala->id)->count()==0){
                        $d=[
                            "numboc"=>$programa->sala->nro.$programa->sala->id.date('Ymd', strtotime($programa->fecha)).$programa->nroFuncion.$programa->price->serie.'-'.$numBoleto,
                            "numero"=>$numBoleto,
                            "fecha"=>now(),
                            "numeroFuncion"=>$programa->nroFuncion,
                            "nombreSala"=>$programa->sala->nombre,
                            "serieTarifa"=>$programa->price->serie,
                            "fechaFuncion"=>$programa->fecha,
                            "horaFuncion"=>$programa->horaInicio,
                            "fila"=>$m->fila,
                            "columna"=>$m->columna,
                            "letra"=>$m->letra,
                            "costo"=>$programa->price->precio,
                            "titulo"=>$m->pelicula,
                            "pelicula_id"=>$m->pelicula_id,
                            "devuelto"=>"0",
                            "idCupon"=>"",
                            "tarjeta"=>"",
                            "credito"=>"",
                            "promo"=>$m->promo,
                            "client_id"=>$client->id,
                            "programa_id"=>$programa->id,
                            "sale_id"=>$sale->id,
                            "price_id"=>$programa->price->id,
                            "sala_id"=>$programa->sala->id,
                            "user_id"=>$user->id,
                        ];
                        array_push($data, $d);
                    }}
                foreach ($request->detalleVenta as $detalle){
                    $d=[
                        'actividadEconomica'=>"590000",
                        'codigoProductoSin'=>"99100",
                        'cantidad'=>$detalle['cantidad'],
                        'precioUnitario'=>$detalle['precio'],
                        'subTotal'=>$detalle['subtotal'],
                        'sale_id'=>$sale->id,
                        'programa_id'=>$detalle['programa_id'],
                        'pelicula_id'=>$detalle['pelicula_id'],
                        'descripcion'=>$detalle['pelicula'],
                    ];
                    array_push($dataDetail, $d);
                }

                Ticket::insert($data);

                Detail::insert($dataDetail);

                $sale->siatEnviado=true;
                $sale->codigoRecepcion=$result->RespuestaServicioFacturacion->codigoRecepcion;
                $sale->cuf=$cuf;
                $sale->save();
                $tickets=Ticket::where('sale_id',$sale->id)->get();
                return response()->json([
                    'sale' => Sale::where('id',$sale->id)->with('client')->with('details')->first(),
                    "tickets"=>$tickets,
                    "error"=>"",
                ]);
            }else{
                return response()->json(['message' => $result->RespuestaServicioFacturacion->mensajesList->descripcion], 400);
            }
        }catch (\Exception $e){
            $sale=new Sale();
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
            $sale->cufd_id=$cufd->id;
            $sale->client_id=$client->id;
            $sale->leyenda=$leyenda;
            $sale->vip=$request->vip;
            $sale->credito=$request->tarjeta;
            $sale->save();

            if ($request->client['email']!==''){
                $details=[
                    "title"=>"Factura",
                    "body"=>"Gracias por su compra",
                    "online"=>false,
                    "anulado"=>false,
                    "cuf"=>"",
                    "numeroFactura"=>"",
                    "sale_id"=>$sale->id,
                    "carpeta"=>"archivos",
                ];
                Mail::to($request->client['email'])->send(new TestMail($details));
            }


            $momentaneos=Momentaneo::where('user_id',$user->id)->get();
            $data=[];
            $dataDetail=[];
            foreach ($momentaneos as $m){
                $programa=Programa::find($m->programa_id);
                $numBoleto=Ticket::where('programa_id',$m->programa_id)->count()+1;
                if(Ticket::where('programa_id',$m->programa_id)
                        ->where("fila",$m->fila)
                        ->where("devuelto",0)
                        ->where("columna",$m->columna)
                        ->where("letra",$m->letra)->where("sala_id",$programa->sala->id)->count()==0){
                    $d=[
                        "numboc"=>$programa->sala->nro.$programa->sala->id.date('Ymd', strtotime($programa->fecha)).$programa->nroFuncion.$programa->price->serie.'-'.$numBoleto,
                        "numero"=>$numBoleto,
                        "fecha"=>now(),
                        "numeroFuncion"=>$programa->nroFuncion,
                        "nombreSala"=>$programa->sala->nombre,
                        "serieTarifa"=>$programa->price->serie,
                        "fechaFuncion"=>$programa->fecha,
                        "horaFuncion"=>$programa->horaInicio,
                        "fila"=>$m->fila,
                        "columna"=>$m->columna,
                        "letra"=>$m->letra,
                        "costo"=>$programa->price->precio,
                        "titulo"=>$m->pelicula,
                        "devuelto"=>"0",
                        "idCupon"=>"",
                        "tarjeta"=>"",
                        "credito"=>"",
                        "promo"=>$m->promo,
                        "client_id"=>$client->id,
                        "programa_id"=>$programa->id,
                        "pelicula_id"=>$m->id,
                        "sale_id"=>$sale->id,
                        "price_id"=>$programa->price->id,
                        "sala_id"=>$programa->sala->id,
                        "user_id"=>$user->id,
                    ];
                    array_push($data, $d);
                }}
            foreach ($request->detalleVenta as $detalle){
                $d=[
                    'actividadEconomica'=>"590000",
                    'codigoProductoSin'=>"99100",
                    'cantidad'=>$detalle['cantidad'],
                    'precioUnitario'=>$detalle['precio'],
                    'subTotal'=>$detalle['subtotal'],
                    'sale_id'=>$sale->id,
                    'programa_id'=>$detalle['programa_id'],
                    'pelicula_id'=>$detalle['pelicula_id'],
                    'descripcion'=>$detalle['pelicula'],
                ];
                array_push($dataDetail, $d);
            }

            Ticket::insert($data);

            Detail::insert($dataDetail);

            $sale->siatEnviado=false;
            $sale->codigoRecepcion="";
            $sale->cuf=$cuf;
            $sale->save();
            $tickets=Ticket::where('sale_id',$sale->id)->get();
            return response()->json([
                'sale' => Sale::where('id',$sale->id)->with('client')->with('details')->first(),
                "tickets"=>$tickets,
                "error"=>"Se creo la venta pero no se pudo enviar a siat!!!",
            ]);
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function  validarTarjeta($codigo){
        $codigo=$this->hexToStr($codigo);
        //return $codigo;
        $res= DB::connection('tarjeta')->table('cliente')->where('codigo',$codigo)->where('estado','ACTIVO')->get();
        if(sizeof($res)>0){
            return $res[0];
        }
        else return 0;

    }

    public function hexToStr($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

    public function enviarCorreo(Request $request){

        if ($request->client['email']!==''){
            $details=[
                "title"=>"Factura",
                "body"=>"Gracias por su compra",
                "anulado"=>false,
                "cuf"=>"",
                "numeroFactura"=>"",
                "sale_id"=>$request->sale['id'],
                "carpeta"=>"archivos",
                "online"=>$request->sale['siatEnviado']
            ];
            Mail::to($request->client['email'])->send(new TestMail($details));
            return true;
        }
        else
            return false;
    }

    public function listBoletos(Request $request){
        return Ticket::where('sale_id',$request->id)->get();
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        //
    }


    public function totalventa(Request $request){
        return Ticket::whereDate('fechaFuncion',$request->fecha)->where('devuelto','0')->count();
    }

    public function datocine(){
        $nit=env('NIT');
        $razon=env("RAZON");
        $dir=env("DIRECCION");
        $tel=env("TELEFONO");
        $url=env("URL_SIAT");
        $url2=env("URL_SIAT2");
        return json_encode(['nit'=>$nit,'razon'=>$razon,'direccion'=>$dir,'telefono'=>$tel,'url'=>$url,'url2'=>$url2]);

    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSaleRequest  $request
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSaleRequest $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */

    public function anularSale(Request $request){
        //return $request->sale['id'];
        if($request->sale['cortesia']=='SI' && $request->sale['tipo']=='BOLETERIA'){
            $sale=Sale::find($request->sale['id']);
            $sale->siatAnulado=1;
            $sale->save();
            DB::SELECT("UPDATE tickets set devuelto=1 where sale_id=".$request->sale['id']);
            return true;
        }
        $codigoAmbiente=env('AMBIENTE');
        $codigoDocumentoSector=1; // 1 compraventa 2 alquiler 23 prevaloradas
        $codigoEmision=1; // 1 online 2 offline 3 masivo
        $codigoModalidad=env('MODALIDAD'); //1 electronica 2 computarizada
        $codigoPuntoVenta=$request->sale['codigoPuntoVenta'];
        $codigoSistema=env('CODIGO_SISTEMA');
        $tipoFacturaDocumento=1; // 1 con credito fiscal 2 sin creditofical 3 nota debito credito
        $codigoSucursal=$request->sale['codigoSucursal'];
        $nit=ENV('NIT');

        $user=User::find($request->user()->id);

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
        if($request->sale['tipo']=='BOLETERIA')
            DB::SELECT("UPDATE tickets set devuelto=1 where sale_id=".$request->sale['id']);
        try {
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
                    "cufd"=>$request->sale['cufd'],
                    "cuis"=>$request->sale['cui'],
                    "nit"=>env('NIT'),
                    "tipoFacturaDocumento"=>$tipoFacturaDocumento,
                    "codigoMotivo"=>$request->motivo['codigoClasificador'],
                    "cuf"=>$request->sale['cuf']
                ]
            ]);
            if($result->RespuestaServicioFacturacion->transaccion){
                $sale=Sale::find($request->sale['id']);
                $sale->siatAnulado=1;
                $sale->save();
                $client=Client::find($sale->client_id);
//                error_log(json_encode($client));
                if ($client->email!=''){
                    $details=[
                        "title"=>"Factura",
                        "body"=>"Factura anulada",
                        "online"=>true,
                        "anulado"=>true,
                        "cuf"=>$sale->cuf,
                        "numeroFactura"=>$sale->numeroFactura,
                        "sale_id"=>$sale->id,
                        "carpeta"=>"archivos",
                    ];
                    Mail::to($client->email)->send(new TestMail($details));
                }

            }
        }catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function cajaBol(Request $request){
        $cadena='';
        if($request->id!=0)  $cadena='and s.user_id=' .$request->id;

        return DB::SELECT("SELECT d.descripcion,d.pelicula_id, sum(d.subTotal) total, sum(d.cantidad) cantidad
        from sales s inner join details d on s.id=d.sale_id
        where
        date(s.fechaEmision) >='$request->ini'
        and date(s.fechaEmision) <='$request->fin'
        ".$cadena."
        and s.tipo='BOLETERIA'
        and s.siatAnulado=false
        GROUP by d.descripcion, d.pelicula_id;");
    }

    public function userbol(Request $request){
        $cadena='';
        if($request->id!=0)  $cadena='and s.user_id=' .$request->id;
        return DB::SELECT("
        SELECT u.name usuario,SUM(S.montoTotal) total
        from users u INNER JOIN sales s on u.id=s.user_id
        where date(s.fechaEmision)>='$request->ini'
        and date(s.fechaEmision)<='$request->fin'
        and s.tipo='BOLETERIA'
        ".$cadena."
        and s.credito='NO'
        and s.vip='NO';
            ");
    }

    public function resumenBol(Request $request){
        $cadena='';
        if($request->id!=0)  $cadena='and s.user_id=' .$request->id;
        return DB::SELECT("
        select
        (SELECT  sum(d.subTotal)
        from sales s inner join details d on s.id=d.sale_id
        where
        date(s.fechaEmision) >='$request->ini'
        and date(s.fechaEmision) <='$request->fin'
        ".$cadena."
        and s.tipo='BOLETERIA'
        and s.siatAnulado=false
        and s.credito='SI') as tarjeta,
        (SELECT  sum(d.subTotal)
        from sales s inner join details d on s.id=d.sale_id
        where
        date(s.fechaEmision) >='$request->ini'
        and date(s.fechaEmision) <='$request->fin'
        ".$cadena."
        and s.tipo='BOLETERIA'
        and s.siatAnulado=false
        and s.vip='SI') as vip,
        (SELECT  sum(d.subTotal)
        from sales s inner join details d on s.id=d.sale_id
        where
        date(s.fechaEmision) >='$request->ini'
        and date(s.fechaEmision) <='$request->fin'
        ".$cadena."
        and s.tipo='BOLETERIA'
        and s.siatAnulado=false
        and s.vip='NO'
		and s.credito='NO'
        and s.cortesia='NO') as efectivo   ");
    }

    public function cajaCandy(Request $request){
        $cadena='';
        if($request->id!=0)  $cadena='and s.user_id=' .$request->id;

        return DB::SELECT("SELECT d.descripcion,d.product_id, sum(d.subTotal) total,sum(d.cantidad) cantidad
        from sales s inner join details d on s.id=d.sale_id
        where date(s.fechaEmision)>='$request->ini'
        and date(s.fechaEmision)<='$request->fin'
        ".$cadena."
        and s.tipo='CANDY'
        and s.siatAnulado=false
        GROUP by  d.product_id");
    }

    public function resumenCandy(Request $request){
        $cadena='';
        if($request->id!=0)  $cadena='and s.user_id=' .$request->id;
        return DB::SELECT("
        select
        (SELECT  sum(d.subTotal)
        from sales s inner join details d on s.id=d.sale_id
        where
        date(s.fechaEmision) >='$request->ini'
        and date(s.fechaEmision) <='$request->fin'
        ".$cadena."
        and s.tipo='CANDY'
        and s.siatAnulado=false
        and s.credito='SI') as tarjeta,
        (SELECT  sum(d.subTotal)
        from sales s inner join details d on s.id=d.sale_id
        where
        date(s.fechaEmision) >='$request->ini'
        and date(s.fechaEmision) <='$request->fin'
        ".$cadena."
        and s.tipo='CANDY'
        and s.siatAnulado=false
        and s.vip='SI') as vip,
        (SELECT  sum(d.subTotal)
        from sales s inner join details d on s.id=d.sale_id
        where
        date(s.fechaEmision) >='$request->ini'
        and date(s.fechaEmision) <='$request->fin'
        ".$cadena."
        and s.tipo='CANDY'
        and s.siatAnulado=false
        and s.vip='NO'
		and s.credito='NO') as efectivo ");
    }

    public function destroy(Sale $sale)
    {
        //
    }
    private function insertarVip(Request $request, Client $client)
    {
        $numeroFactura=0;
        $codigoSucursal=0;
        $codigoPuntoVenta=0;
        $codigoDocumentoSector=0;
        $sale=new Sale();
        $sale->numeroFactura=$numeroFactura;
        $sale->cuf="";
        $sale->cufd="";
        $sale->cui="";
        $sale->codigoSucursal=$codigoSucursal;
        $sale->codigoPuntoVenta=$codigoPuntoVenta;
        $sale->fechaEmision=now();
        $sale->montoTotal=$request->montoTotal;
        $sale->usuario=$request->user()->name;
        $sale->codigoDocumentoSector=$codigoDocumentoSector;
        $sale->user_id=$request->user()->id;
        $sale->cufd_id=null;
        $sale->client_id=$client->id;
        $sale->leyenda="";
        $sale->vip=$request->vip;
        $sale->credito=$request->tarjeta;
        $sale->save();

        $user=User::find($request->user()->id);

        $momentaneos=Momentaneo::where('user_id',$user->id)->get();
        $data=[];
        $dataDetail=[];
        foreach ($momentaneos as $m){
            $programa=Programa::find($m->programa_id);
            $numBoleto=Ticket::where('programa_id',$m->programa_id)->count()+1;
            if(Ticket::where('programa_id',$m->programa_id)
                    ->where("fila",$m->fila)
                    ->where("devuelto",0)
                    ->where("columna",$m->columna)
                    ->where("letra",$m->letra)->where("sala_id",$programa->sala->id)->count()==0){
                $d=[
                    "numboc"=>$programa->sala->nro.$programa->sala->id.date('Ymd', strtotime($programa->fecha)).$programa->nroFuncion.$programa->price->serie.'-'.$numBoleto,
                    "numero"=>$numBoleto,
                    "fecha"=>now(),
                    "numeroFuncion"=>$programa->nroFuncion,
                    "nombreSala"=>$programa->sala->nombre,
                    "serieTarifa"=>$programa->price->serie,
                    "fechaFuncion"=>$programa->fecha,
                    "horaFuncion"=>$programa->horaInicio,
                    "fila"=>$m->fila,
                    "columna"=>$m->columna,
                    "letra"=>$m->letra,
                    "costo"=>$programa->price->precio,
                    "titulo"=>$m->pelicula,
                    "devuelto"=>"0",
                    "idCupon"=>"",
                    "tarjeta"=>"",
                    "credito"=>"",
                    "promo"=>$m->promo,
                    "client_id"=>$client->id,
                    "programa_id"=>$programa->id,
                    "pelicula_id"=>$m->id,
                    "sale_id"=>$sale->id,
                    "price_id"=>$programa->price->id,
                    "sala_id"=>$programa->sala->id,
                    "user_id"=>$user->id,
                ];
                array_push($data, $d);
            }}
        foreach ($request->detalleVenta as $detalle){
            $d=[
                'actividadEconomica'=>"590000",
                'codigoProductoSin'=>"99100",
                'cantidad'=>$detalle['cantidad'],
                'precioUnitario'=>$detalle['precio'],
                'subTotal'=>$detalle['subtotal'],
                'sale_id'=>$sale->id,
                'programa_id'=>$detalle['programa_id'],
                'pelicula_id'=>$detalle['pelicula_id'],
                'descripcion'=>$detalle['pelicula'],
            ];
            array_push($dataDetail, $d);
        }

        Ticket::insert($data);

        Detail::insert($dataDetail);

        $sale->siatEnviado=true;
        $sale->codigoRecepcion="";
        $sale->cuf="";
        $sale->save();
        $tickets=Ticket::where('sale_id',$sale->id)->get();
        $codigo=$this->hexToStr($request->codigoTarjeta);
        DB::connection('tarjeta')->select("
            UPDATE cliente SET saldo=saldo-$sale->montoTotal WHERE codigo='$codigo'
        ");
        $fecha=date('Y-m-d');
        $monto=$sale->montoTotal;
        $numero=$sale->id;
        $cliente=$client->id;
        DB::connection('tarjeta')->select("
INSERT INTO historial (fecha, lugar, monto, numero, cliente_id) VALUES ('$fecha', 'BOLETERIA', $monto, $numero, $cliente)
        ");
        $sale=Sale::where('id',$sale->id)->with('client')->with('details')->first();
        $sale->siatEnviado=false;
        return response()->json([
            'sale' => $sale,
            "tickets"=>$tickets,
            "error"=>"Se creo la venta!!!",
        ]);
//        return response()->json(['message' => $e->getMessage()], 500);
    }

    private function insertarCortesia(Request $request, Client $client)
    {
        $numeroFactura=0;
        $codigoSucursal=0;
        $codigoPuntoVenta=0;
        $codigoDocumentoSector=0;
        $sale=new Sale();
        $sale->numeroFactura=$numeroFactura;
        $sale->cuf="";
        $sale->cufd="";
        $sale->cui="";
        $sale->codigoSucursal=$codigoSucursal;
        $sale->codigoPuntoVenta=$codigoPuntoVenta;
        $sale->fechaEmision=now();
        $sale->montoTotal=0;
        $sale->usuario=$request->user()->name;
        $sale->codigoDocumentoSector=$codigoDocumentoSector;
        $sale->user_id=$request->user()->id;
        $sale->cufd_id=null;
        $sale->client_id=$client->id;
        $sale->leyenda="";
        $sale->vip=$request->vip;
        $sale->credito=$request->tarjeta;
        $sale->cortesia='SI';
        $sale->save();

        $user=User::find($request->user()->id);

        $momentaneos=Momentaneo::where('user_id',$user->id)->get();
        $data=[];
        $dataDetail=[];
        foreach ($momentaneos as $m){
            $programa=Programa::find($m->programa_id);
            $numBoleto=Ticket::where('programa_id',$m->programa_id)->count()+1;
            if(Ticket::where('programa_id',$m->programa_id)
                    ->where("fila",$m->fila)
                    ->where("devuelto",0)
                    ->where("columna",$m->columna)
                    ->where("letra",$m->letra)->where("sala_id",$programa->sala->id)->count()==0){
                $d=[
                    "numboc"=>$programa->sala->nro.$programa->sala->id.date('Ymd', strtotime($programa->fecha)).$programa->nroFuncion.$programa->price->serie.'-'.$numBoleto,
                    "numero"=>$numBoleto,
                    "fecha"=>now(),
                    "numeroFuncion"=>$programa->nroFuncion,
                    "nombreSala"=>$programa->sala->nombre,
                    "serieTarifa"=>$programa->price->serie,
                    "fechaFuncion"=>$programa->fecha,
                    "horaFuncion"=>$programa->horaInicio,
                    "fila"=>$m->fila,
                    "columna"=>$m->columna,
                    "letra"=>$m->letra,
                    "costo"=>$programa->price->precio,
                    "titulo"=>$m->pelicula,
                    "devuelto"=>"0",
                    "idCupon"=>"",
                    "tarjeta"=>"",
                    "credito"=>"",
                    //"cortesia"=>"SI",
                    "promo"=>$m->promo,
                    "client_id"=>$client->id,
                    "programa_id"=>$programa->id,
                    "pelicula_id"=>$m->id,
                    "sale_id"=>$sale->id,
                    "price_id"=>$programa->price->id,
                    "sala_id"=>$programa->sala->id,
                    "user_id"=>$user->id,
                ];
                array_push($data, $d);
            }}
        foreach ($request->detalleVenta as $detalle){
            $d=[
                'actividadEconomica'=>"590000",
                'codigoProductoSin'=>"99100",
                'cantidad'=>$detalle['cantidad'],
                'precioUnitario'=>0,
                'subTotal'=>0,
                'sale_id'=>$sale->id,
                'programa_id'=>$detalle['programa_id'],
                'pelicula_id'=>$detalle['pelicula_id'],
                'descripcion'=>$detalle['pelicula'],
            ];
            array_push($dataDetail, $d);
        }

        Ticket::insert($data);

        Detail::insert($dataDetail);

        $sale->siatEnviado=true;
        $sale->codigoRecepcion="";
        $sale->cuf="";
        $sale->save();
        $tickets=Ticket::where('sale_id',$sale->id)->get();

        foreach ($request->frees as $f){
            if ($f['status']==1){
                $cortesia=Cortesia::find($f['id']);
                $cortesia->date=now();
                $cortesia->time=now();
                $cortesia->user_id=$request->user()->id;
                $cortesia->sale_id=$sale->id;
                $cortesia->save();
            }

        }
        $sale=Sale::where('id',$sale->id)->with('client')->with('details')->first();
        $sale->siatEnviado=false;
        return response()->json([
            'sale' => $sale,
            "tickets"=>$tickets,
            "error"=>"Se creo la venta!!!",
        ]);
//        return response()->json(['message' => $e->getMessage()], 500);
    }

    public function validanit($nit){
        $cui=Cui::where('codigoPuntoVenta', 0)->where('codigoSucursal', 0)->where('fechaVigencia','>=', now());
        $client = new \SoapClient(env("URL_SIAT")."FacturacionCodigos?WSDL",  [
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
        $result= $client->verificarNit ([
            "SolicitudVerificarNit"=>[
                "codigoAmbiente"=>env('AMBIENTE'),
                "codigoSistema"=>env('CODIGO_SISTEMA'),
                "nit"=>env('NIT'),
                "cuis"=> $cui->first()->codigo,
                "codigoModalidad"=>env('MODALIDAD'),
                "codigoSucursal"=>0,
                "nitParaVerificacion"=>$nit
            ]
        ]);
        return $result;
    }

    private function insertarRecibo(Request $request, $client)
    {
        $numeroFactura=0;
        $codigoSucursal=0;
        $codigoPuntoVenta=0;
        $codigoDocumentoSector=0;
        $sale=new Sale();
        $sale->numeroFactura=$numeroFactura;
        $sale->cuf="";
        $sale->cufd="";
        $sale->cui="";
        $sale->codigoSucursal=$codigoSucursal;
        $sale->codigoPuntoVenta=$codigoPuntoVenta;
        $sale->fechaEmision=now();
        $sale->montoTotal=$request->montoTotal;
        $sale->usuario=$request->user()->name;
        $sale->codigoDocumentoSector=$codigoDocumentoSector;
        $sale->user_id=$request->user()->id;
        $sale->cufd_id=null;
        $sale->client_id=$client->id;

        $sale->leyenda="";
        $sale->venta="R";
        $sale->vip=$request->vip;
        $sale->credito=$request->tarjeta;
        $sale->cortesia='SI';
        $sale->save();


        $user=User::find($request->user()->id);

        $momentaneos=Momentaneo::where('user_id',$user->id)->get();
        $data=[];
        $dataDetail=[];
        foreach ($momentaneos as $m){
            $programa=Programa::find($m->programa_id);
            $numBoleto=Ticket::where('programa_id',$m->programa_id)->count()+1;
            if(Ticket::where('programa_id',$m->programa_id)
                    ->where("fila",$m->fila)
                    ->where("devuelto",0)
                    ->where("columna",$m->columna)
                    ->where("letra",$m->letra)->where("sala_id",$programa->sala->id)->count()==0){
                $d=[
                    "numboc"=>$programa->sala->nro.$programa->sala->id.date('Ymd', strtotime($programa->fecha)).$programa->nroFuncion.$programa->price->serie.'-'.$numBoleto,
                    "numero"=>$numBoleto,
                    "fecha"=>now(),
                    "numeroFuncion"=>$programa->nroFuncion,
                    "nombreSala"=>$programa->sala->nombre,
                    "serieTarifa"=>$programa->price->serie,
                    "fechaFuncion"=>$programa->fecha,
                    "horaFuncion"=>$programa->horaInicio,
                    "fila"=>$m->fila,
                    "columna"=>$m->columna,
                    "letra"=>$m->letra,
                    "costo"=>$programa->price->precio,
                    "titulo"=>$m->pelicula,
                    "devuelto"=>"0",
                    "idCupon"=>"",
                    "tarjeta"=>"",
                    "credito"=>"",
                    //"cortesia"=>"SI",
                    "promo"=>$m->promo,
                    "client_id"=>$client->id,
                    "programa_id"=>$programa->id,
                    "pelicula_id"=>$m->id,
                    "sale_id"=>$sale->id,
                    "price_id"=>$programa->price->id,
                    "sala_id"=>$programa->sala->id,
                    "user_id"=>$user->id,
                ];
                array_push($data, $d);
            }}
        foreach ($request->detalleVenta as $detalle){
            $d=[
                'actividadEconomica'=>"590000",
                'codigoProductoSin'=>"99100",
                'cantidad'=>$detalle['cantidad'],
                'precioUnitario'=>$detalle['subtotal'],
                'subTotal'=>$detalle['subtotal'],
                'sale_id'=>$sale->id,
                'programa_id'=>$detalle['programa_id'],
                'pelicula_id'=>$detalle['pelicula_id'],
                'descripcion'=>$detalle['pelicula'],
            ];
            array_push($dataDetail, $d);
        }

        Ticket::insert($data);

        Detail::insert($dataDetail);

        $sale->siatEnviado=true;
        $sale->codigoRecepcion="";
        $sale->cuf="";
        $sale->save();
        $tickets=Ticket::where('sale_id',$sale->id)->get();

        foreach ($request->frees as $f){
            if ($f['status']==1){
                $cortesia=Cortesia::find($f['id']);
                $cortesia->date=now();
                $cortesia->time=now();
                $cortesia->user_id=$request->user()->id;
                $cortesia->sale_id=$sale->id;
                $cortesia->save();
            }

        }
        $sale=Sale::where('id',$sale->id)->with('client')->with('details')->first();
        $sale->siatEnviado=false;
        return response()->json([
            'sale' => $sale,
            "tickets"=>$tickets,
            "error"=>"Se creo la venta!!!",
        ]);
//        return response()->json(['message' => $e->getMessage()], 500);
    }
}

