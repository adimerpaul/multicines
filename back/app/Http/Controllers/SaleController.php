<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Cufd;
use App\Models\Cui;
use App\Models\Detail;
use App\Models\Momentaneo;
use App\Models\Programa;
use App\Models\Sale;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        return Programa::select('movie_id')->groupBy('movie_id')->with('movie')->whereDate('fecha',$request->fecha)->get();
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
               IF((SELECT COUNT(*) FROM tickets t WHERE t.programa_id=p.id AND t.fila=a.fila AND t.columna=a.columna AND t.letra=a.letra)=1, 'OCUPADO',
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
        if (Client::where('complemento',$request->client['complemento'])->where('numeroDocumento',$request->client['numeroDocumento'])->exists()) {
            $client=Client::find($request->client['id']);
            $client->nombreRazonSocial=$request->client['nombreRazonSocial'];
            $client->codigoTipoDocumentoIdentidad=$request->client['codigoTipoDocumentoIdentidad'];
            $client->save();
        }else if(Client::where('numeroDocumento',$request->client['numeroDocumento'])->exists()){
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

        $codigoPuntoVenta=0;
        $codigoSucursal=0;
        $codigoDocumentoSector=1;
        $user=(object)["name"=>"admin","id"=>1];

        if (Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->whereDate('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No existe CUI para la venta!!'], 400);
        }
        if (Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->whereDate('fechaVigencia','>=', now())->count()==0){
            return response()->json(['message' => 'No exite CUFD para la venta!!'], 400);
        }
        $cui=Cui::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->whereDate('fechaVigencia','>=', now())->first();
        $cufd=Cufd::where('codigoPuntoVenta', $codigoPuntoVenta)->where('codigoSucursal', $codigoSucursal)->whereDate('fechaVigencia','>=', now())->first();
        if (Sale::where('cui', $cui->codigo)->count()==0){
            $numeroFactura=1;
        }else{
            $sale=sale::where('cui',$cui->codigo)->orderBy('numeroFactura', 'desc')->first();
            $numeroFactura=$sale->numeroFactura+1;
        }
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
        $sale->client_id=$client->id;
        $sale->save();
        $momentaneos=Momentaneo::where('user_id',$user->id)->get();
//        return $momentaneos;
        $data=[];
        $dataDetail=[];

        foreach ($momentaneos as $m){
            $programa=Programa::find($m->programa_id);
            $numBoleto=Ticket::where('programa_id',$m->programa_id)->count()+1;
//            return $programa->sala;
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
                "devuelto"=>"",
                "idCupon"=>"",
                "tarjeta"=>"",
                "credito"=>"",
                "client_id"=>$client->id,
                "programa_id"=>$programa->id,
                "sale_id"=>$sale->id,
                "price_id"=>$programa->price->id,
                "sala_id"=>$programa->sala->id,
                "user_id"=>$user->id,
            ];
            array_push($data, $d);
        }
//        return $request->detalleVenta ;
        foreach ($request->detalleVenta as $detalle){
            $d=[
                'actividadEconomica'=>"590000",
                'codigoProductoSin'=>"99100",
                'cantidad'=>$detalle['cantidad'],
                'precioUnitario'=>$detalle['precio'],
                'subTotal'=>$detalle['subtotal'],
                'sale_id'=>$sale->id,
            ];
            array_push($dataDetail, $d);
        }
        Ticket::insert($data);
        Detail::insert($dataDetail);
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
    public function destroy(Sale $sale)
    {
        //
    }
}
