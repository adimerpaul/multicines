<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoSignificativo extends Model
{
    use HasFactory;
    protected $fillable = [
        'codigoPuntoVenta',
        'codigoSucursal',
        'fechaHoraFinEvento',
        'fechaHoraInicioEvento',
        'codigoMotivoEvento',
        'descripcion',
        'cufd',
        'cufdEvento',
        'codigoRecepcionEventoSignificativo',
    ];
}
