<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model implements AuditableContract
{
    use Auditable;
    use HasFactory;
    use SoftDeletes;
    protected $fillable= [
        'nombreRazonSocial',
        'codigoTipoDocumentoIdentidad',
        'numeroDocumento',
        'complemento',
        'email',
    ];
}

