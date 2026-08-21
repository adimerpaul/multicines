<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Cortesia extends Model implements AuditableContract
{
    use SoftDeletes;
    use Auditable;
    use HasFactory;
    protected $fillable = [
        'date',
        'time',
        'user_id',
        'sale_id',
    ];
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function sale(){
        return $this->belongsTo('App\Models\Sale');
    }
}
