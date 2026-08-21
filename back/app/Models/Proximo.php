<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proximo extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = ['nombre','descripcion','fecha','start','director','imagen','trailer','estado','minutos','pais','clasificacion','generos'];
    protected $hidden = ['created_at', 'updated_at'];
    public function casts(){
        return $this->hasMany(Cast::class);
    }
}
