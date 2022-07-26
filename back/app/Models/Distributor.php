<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable=['nombre','duracion','paisOrigen','genero','sipnosis','urlTrailer','imagen','clasificacion','fechaEstreno','distributor_id'];
/*
$table->string("nombre")->nullable();
$table->string("dir")->nullable();
$table->string("loc")->nullable();
$table->string("nit")->nullable();
$table->string("tel")->nullable();
$table->string("email")->nullable();
$table->string("responsable")->nullable();*/
}
