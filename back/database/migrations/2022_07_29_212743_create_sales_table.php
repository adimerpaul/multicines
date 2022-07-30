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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string("numeroFactura")->nullable();
            $table->string("cuf")->nullable();
            $table->string("cufd")->nullable();
            $table->integer("codigoSucursal")->nullable();
            $table->integer("codigoPuntoVenta")->nullable();
            $table->dateTime("fechaEmision")->nullable();
            $table->double("montoTotal",11,2)->nullable();
            $table->string("usuario")->nullable();
            $table->integer("codigoDocumentoSector")->nullable();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on("users");
            $table->unsignedBigInteger("client_id")->nullable();
            $table->foreign("client_id")->references("id")->on("clients");
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
        Schema::dropIfExists('sales');
    }
};
