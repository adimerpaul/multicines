<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable=['fila','columna','letra','activo',"sala_id"];
    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }
}
