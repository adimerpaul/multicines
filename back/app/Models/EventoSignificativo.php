<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventoSignificativo extends Model
{
    use SoftDeletes;
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
        'cufd_id'
    ];
    public function cufd()
    {
        return $this->belongsTo(Cufd::class)->with(array('sales' => function($query) {
        $query->where('siatEnviado', false);
    }));
    }
}
