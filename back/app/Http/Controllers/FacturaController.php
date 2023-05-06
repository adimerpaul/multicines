<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Http\Requests\StoreFacturaRequest;
use App\Http\Requests\UpdateFacturaRequest;
use App\Imports\UsersImport;
use App\Models\Sale;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
class FacturaController extends Controller{
    public function index(){ return Factura::all(); }
    public function show(Factura $factura){ return $factura; }
    public function store(StoreFacturaRequest $request){ return Factura::create($request->all()); }
    public function update(UpdateFacturaRequest $request, Factura $factura){ return $factura->update($request->all()); }
    public function destroy(Factura $factura){ return $factura->delete(); }

    public function getYearMonthFacturas(Request $request){
        $year=$request->anio;
        $month=$request->mes;
        $facturas=Factura::whereYear('fecha', '=', $year)->whereMonth('fecha', '=', $month)->get();
        return $facturas;
    }

    public function uploadFileZip(Request $request)
    {
        $file = $request->file('archivo');
        $fileName = 'archivoVentas' . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('VentasXlsx'), $fileName);
        return $fileName;
    }
    public function unzipFile($fileName)
    {
        $zip = new \ZipArchive();
        $zip->open('VentasXlsx/'.$fileName); // open the zip file to extract
        $zip->extractTo('VentasXlsx');
        $zip->close();
    }

    public function import(Request $request)
    {
        $fileName=$this->uploadFileZip($request);
        error_log($fileName);
        $this->unzipFile($fileName);
        $array = Excel::toArray(new UsersImport, 'VentasXlsx/archivoVentas.xlsx');
        foreach ($array[0] as $row) {
            $factura= Factura::where('nFactura', $row['no_de_la_factura'])->where('cuf', $row['codigo_de_autorizacion'])->first();
            error_log($row['no']);
            if($factura) {
//                $factura->n = $row['no'];
//                $factura->fecha = $this->convertDate($row['fecha_de_la_factura']);
//                $factura->nFactura = $row['no_de_la_factura'];
//                $factura->cuf=$row['codigo_de_autorizacion'];
//                $factura->nit=$row['nit_ci_cliente'];
//                $factura->complemento=$row['complemento'];
//                $factura->nombre=$row['nombre_o_razon_social'];
//                $factura->importe=$row['importe_total_de_la_venta'];
//                $factura->ice=$row['importe_ice'];
//                $factura->iehd=$row['importe_iehd'];
//                $factura->ipj=$row['importe_ipj'];
//                $factura->tasas=$row['tasas'];
//                $factura->noSujeto=$row['otros_no_sujetos_al_iva'];
//                $factura->exentas=$row['exportaciones_y_operaciones_exentas'];
//                $factura->tasaCero=$row['ventas_gravadas_a_tasa_cero'];
//                $factura->subTotal=$row['subtotal'];
//                $factura->rebajas=$row['descuentos_bonificaciones_y_rebajas_sujetas_al_iva'];
//                $factura->card=$row['importe_gift_card'];
//                $factura->importeBase=$row['importe_base_para_debito_fiscal'];
//                $factura->iva=$row['debito_fiscal'];
//                $factura->estado=$row['estado'];
//                $factura->codigoControl=$row['codigo_de_control'];
//                $factura->tipoVenta=$row['tipo_de_venta'];
//                $factura->derecho=$row['con_derecho_a_credito_fiscal'];
//                $factura->consolidado=$row['estado_consolidacion'];
//                $factura->save();
            }else{
                $f = new Factura();
                $f->n = $row['no'];
                $f->fecha = $this->convertDate($row['fecha_de_la_factura']);
                $f->nFactura = $row['no_de_la_factura'];
                $f->cuf=$row['codigo_de_autorizacion'];
                $f->nit=$row['nit_ci_cliente'];
                $f->complemento=$row['complemento'];
                $f->nombre=$row['nombre_o_razon_social'];
                $f->importe=$row['importe_total_de_la_venta'];
                $f->ice=$row['importe_ice'];
                $f->iehd=$row['importe_iehd'];
                $f->ipj=$row['importe_ipj'];
                $f->tasas=$row['tasas'];
                $f->noSujeto=$row['otros_no_sujetos_al_iva'];
                $f->exentas=$row['exportaciones_y_operaciones_exentas'];
                $f->tasaCero=$row['ventas_gravadas_a_tasa_cero'];
                $f->subTotal=$row['subtotal'];
                $f->rebajas=$row['descuentos_bonificaciones_y_rebajas_sujetas_al_iva'];
                $f->card=$row['importe_gift_card'];
                $f->importeBase=$row['importe_base_para_debito_fiscal'];
                $f->iva=$row['debito_fiscal'];
                $f->estado=$row['estado'];
                $f->codigoControl=$row['codigo_de_control'];
                $f->tipoVenta=$row['tipo_de_venta'];
                $f->derecho=$row['con_derecho_a_credito_fiscal'];
                $f->consolidado=$row['estado_consolidacion'];
                $exitCuf=Sale::where('cuf', trim($row['codigo_de_autorizacion']))->exists();
                $f->impuesto=($exitCuf)?'SI':'NO';
                $f->save();
            }
//            echo $this->convertDate($row['fecha_de_la_factura']).'<br>';
        }
//        Factura::create($facturas);
    }
    public function convertDate($date){
        $date = str_replace('/', '-', $date);
        return date('Y-m-d', strtotime($date));
    }

}
