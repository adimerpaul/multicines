<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('numboc')->nullable();
            $table->string('numero')->nullable();
            $table->date('fecha')->nullable();
            $table->string('numeroFuncion')->nullable();
            $table->string('nombreSala')->nullable();
            $table->string('serieTarifa')->nullable();
            $table->date('fechaFuncion')->nullable();
            $table->datetime('horaFuncion')->nullable();
            $table->string('fila')->nullable();
            $table->string('columna')->nullable();
            $table->string('costo')->nullable();
            $table->string('titulo')->nullable();
            $table->string('devuelto')->nullable();
//            $table->string('idCupon')->nullable();
            $table->string('tarjeta')->nullable();
            $table->string('credito')->nullable();
            $table->unsignedBigInteger("client_id")->nullable();
            $table->foreign("client_id")->references("id")->on("clients");
            $table->unsignedBigInteger("programa_id")->nullable();
            $table->foreign("programa_id")->references("id")->on("programas");
            $table->unsignedBigInteger("seat_id")->nullable();
            $table->foreign("seat_id")->references("id")->on("seats");
            $table->unsignedBigInteger("sale_id")->nullable();
            $table->foreign("sale_id")->references("id")->on("sales");
            $table->unsignedBigInteger("price_id")->nullable();
            $table->foreign("price_id")->references("id")->on("prices");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};