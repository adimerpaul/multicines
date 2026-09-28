<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Ticket extends Model implements AuditableContract
{
    use SoftDeletes;
    use Auditable;
    use HasFactory;
    /*
$table->string('numboc')->nullable();
$table->string('numero')->nullable();
$table->date('fecha')->nullable();
$table->string('numeroFuncion')->nullable();
$table->string('nombreSala')->nullable();
$table->string('serieTarifa')->nullable();
$table->date('fechaFuncion')->nullable();
$table->datetime('horaFuncion')->nullable();
$table->integer('fila')->nullable();
$table->integer('columna')->nullable();
$table->string('letra')->nullable();
$table->string('costo')->nullable();
$table->string('titulo')->nullable();
$table->string('devuelto')->nullable();
$table->string('idCupon')->nullable();
$table->string('tarjeta')->nullable();
$table->string('credito')->nullable();
$table->unsignedBigInteger("client_id")->nullable();
$table->unsignedBigInteger("programa_id")->nullable();
$table->unsignedBigInteger("seat_id")->nullable();
$table->unsignedBigInteger("sale_id")->nullable();
$table->unsignedBigInteger("price_id")->nullable();
$table->unsignedBigInteger("sala_id")->nullable();*/

    /**
     * Imagenes de pie de boleto de la pelicula del boleto (tickets.pelicula_id).
     */
    public function footerImages()
    {
        return $this->hasMany(MovieFooterImage::class, 'movie_id', 'pelicula_id')
            ->orderBy('orden')
            ->orderBy('id');
    }
}
