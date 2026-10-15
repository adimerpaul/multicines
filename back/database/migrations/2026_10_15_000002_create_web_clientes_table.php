<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clientes de la web publica (login con Google via Firebase).
 * Tabla separada de `users` (personal del cine) para que no se mezclen.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('web_clientes')) {
            Schema::create('web_clientes', function (Blueprint $table) {
                $table->id();
                $table->string('firebase_uid', 128)->unique();
                $table->string('proveedor', 50)->default('google.com');
                $table->string('email')->nullable()->index();
                $table->boolean('email_verificado')->default(false);
                $table->string('nombre')->nullable();
                $table->string('foto', 500)->nullable();
                $table->string('telefono', 30)->nullable();
                // Datos de facturacion para autocompletar la siguiente compra
                $table->string('nit', 20)->nullable();
                $table->string('complemento', 5)->nullable();
                $table->string('razon_social', 120)->nullable();
                $table->string('estado', 20)->default('ACTIVO');
                $table->unsignedInteger('logins')->default(0);
                $table->timestamp('ultimo_login_at')->nullable();
                $table->string('ultimo_login_ip', 45)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('sales', 'web_cliente_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->unsignedBigInteger('web_cliente_id')->nullable()->after('client_id');
                $table->foreign('web_cliente_id')->references('id')->on('web_clientes')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'web_cliente_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['web_cliente_id']);
                $table->dropColumn('web_cliente_id');
            });
        }
        Schema::dropIfExists('web_clientes');
    }
};
