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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string("n");
            $table->date("fecha");
            $table->string("nFactura");
            $table->string("cuf");
            $table->string("nit");
            $table->string("complemento");
            $table->string("nombre");
            $table->string("importe");
            $table->string("ice");
            $table->string("iehd");
            $table->string("ipj");
            $table->string("tasas");
            $table->string("noSujeto");
            $table->string("exentas");
            $table->string("tasaCero");
            $table->string("subTotal");
            $table->string("rebajas");
            $table->string("card");
            $table->string("importeBase");
            $table->string("iva");
            $table->string("estado");
            $table->string("codigoControl");
            $table->string("tipoVenta");
            $table->string("derecho");
            $table->string("consilidado");
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
        Schema::dropIfExists('facturas');
    }
};
