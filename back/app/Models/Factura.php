<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        "n",
        "fecha",
        "nFactura",
        "cuf",
        "nit",
        "complemento",
        "nombre",
        "importe",
        "ice",
        "iehd",
        "ipj",
        "tasas",
        "noSujeto",
        "exentas",
        "tasaCero",
        "subTotal",
        "rebajas",
        "card",
        "importeBase",
        "iva",
        "estado",
        "codigoControl",
        "tipoVenta",
        "derecho",
        "consolidado",
        "impuesto",
    ];
}
