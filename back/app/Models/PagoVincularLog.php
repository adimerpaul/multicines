<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PagoVincularLog extends Model
{
    use SoftDeletes;
    protected $table = 'pago_vincular_logs';

    protected $fillable = [
        'sale_id',
        'qr_id',
        'transaction_id',
        'user_id',
        'user_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
