<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificado extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'archivo', 'titular', 'organizacion', 'cargo', 'nit', 'email', 'emisor', 'serial',
        'fechaInicio', 'fechaFin', 'publicKey', 'privateKey', 'p12Path', 'activo',
        'fechaActivacion', 'user_id',
    ];

    // La llave privada y el archivo .p12 nunca se envian al frontend
    protected $hidden = ['privateKey', 'p12Path'];

    protected $casts = [
        'privateKey' => 'encrypted',
        'activo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
