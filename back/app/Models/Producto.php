<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model implements AuditableContract
{
    use Auditable;
    use HasFactory;
    use SoftDeletes;
    use HasFactory;
    protected $fillable=['nombre','descripcion','precio','activo','imagen','color',"rubro_id"];
    public function rubro()
    {
        return $this->belongsTo(Rubro::class);
    }
}
