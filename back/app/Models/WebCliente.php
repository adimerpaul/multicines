<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Cliente de la web publica. No es un User del sistema: entra con Google
 * (Firebase solo valida la identidad) y todo lo demas vive en la base local.
 */
class WebCliente extends Model
{
    use SoftDeletes;

    protected $table = 'web_clientes';

    protected $fillable = [
        'firebase_uid',
        'proveedor',
        'email',
        'email_verificado',
        'nombre',
        'foto',
        'telefono',
        'nit',
        'complemento',
        'razon_social',
        'estado',
        'logins',
        'ultimo_login_at',
        'ultimo_login_ip',
    ];

    protected $hidden = ['firebase_uid', 'ultimo_login_ip', 'deleted_at'];

    protected $casts = [
        'email_verificado' => 'boolean',
        'ultimo_login_at' => 'datetime',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'web_cliente_id');
    }

    /** Datos que se envian al navegador. */
    public function publico(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre ?: $this->email,
            'email' => $this->email,
            'foto' => $this->foto,
            'nit' => $this->nit,
            'complemento' => $this->complemento,
            'razonSocial' => $this->razon_social,
        ];
    }
}
