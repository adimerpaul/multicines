<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiatToken extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'token', 'descripcion', 'email', 'nit', 'subsistema', 'fechaEmision', 'fechaVigencia',
        'activo', 'fechaActivacion', 'user_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // El token completo no se envia al frontend; solo una version recortada
    protected $hidden = ['token'];
    protected $appends = ['tokenCorto'];

    private static $cache = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTokenCortoAttribute()
    {
        $t = $this->attributes['token'] ?? '';
        return strlen($t) > 30 ? substr($t, 0, 15) . '…' . substr($t, -10) : $t;
    }

    /**
     * Token SIAT en uso. Lo lee de la BD (token activo); si no hay, usa el TOKEN del .env.
     */
    public static function actual()
    {
        if (self::$cache === null) {
            try {
                self::$cache = self::where('activo', true)->orderBy('id', 'desc')->value('token') ?: env('TOKEN');
            } catch (\Throwable $e) {
                self::$cache = env('TOKEN');
            }
        }
        return self::$cache;
    }

    public static function limpiarCache()
    {
        self::$cache = null;
    }

    /**
     * Decodifica el payload del JWT (sin verificar firma) y devuelve los datos utiles.
     * Devuelve null si el texto no es un JWT valido.
     */
    public static function datosDesdeJwt($jwt)
    {
        $partes = explode('.', trim($jwt));
        if (count($partes) !== 3) {
            return null;
        }
        $payload = json_decode(base64_decode(strtr($partes[1], '-_', '+/')), true);
        if (!is_array($payload)) {
            return null;
        }
        return [
            'email' => $payload['sub'] ?? null,
            'nit' => isset($payload['nitDelegado']) ? (string) $payload['nitDelegado'] : null,
            'subsistema' => $payload['subsistema'] ?? null,
            'fechaEmision' => isset($payload['iat']) ? date('Y-m-d H:i:s', $payload['iat']) : null,
            'fechaVigencia' => isset($payload['exp']) ? date('Y-m-d H:i:s', $payload['exp']) : null,
        ];
    }
}
