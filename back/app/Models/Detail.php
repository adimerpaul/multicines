<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Detail extends Model implements AuditableContract
{
    use SoftDeletes;
    use Auditable;
    use HasFactory;
    protected $fillable = [
        'actividadEconomica',
        'codigoProductoSin',
        'cantidad',
        'precioUnitario',
        'subTotal',
        'sale_id',
        'programa_id',
        'pelicula_id',
        'descripcion',
    ];
}
