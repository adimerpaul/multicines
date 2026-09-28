<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Imagenes de pie de boleto por pelicula (1:N). Son PNG (redondeados, con
 * fondo transparente) que se imprimen abajo del boleto: formatos, logos de
 * sala, patrocinadores, etc. Una pelicula puede tener cualquier cantidad.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('movie_footer_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movie_id');
            $table->foreign('movie_id')->references('id')->on('movies')->onDelete('cascade');
            $table->string('imagen');
            $table->string('nombre')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['movie_id', 'orden']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('movie_footer_images');
    }
};
