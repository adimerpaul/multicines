<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Venta de entradas por la web:
 * - sales.canal: 'WEB' para las ventas de la pagina publica (null = boleteria).
 * - sales.codigo_entrada: codigo unico que va en el QR de la entrada.
 * - sales.verificado*: la entrada se valido en la puerta de la sala.
 * - momentaneos.web_compra: asientos retenidos mientras el cliente web paga.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'canal')) {
                $table->string('canal', 10)->nullable()->after('tipo')->index();
            }
            if (!Schema::hasColumn('sales', 'codigo_entrada')) {
                $table->string('codigo_entrada', 20)->nullable()->unique()->after('canal');
            }
            if (!Schema::hasColumn('sales', 'verificado')) {
                $table->boolean('verificado')->default(false)->after('codigo_entrada');
                $table->dateTime('verificado_at')->nullable()->after('verificado');
                $table->unsignedBigInteger('verificado_user_id')->nullable()->after('verificado_at');
            }
        });

        if (!Schema::hasColumn('momentaneos', 'web_compra')) {
            Schema::table('momentaneos', function (Blueprint $table) {
                $table->string('web_compra', 40)->nullable()->index();
            });
        }
    }

    public function down()
    {
        Schema::table('momentaneos', function (Blueprint $table) {
            $table->dropIndex(['web_compra']);
            $table->dropColumn('web_compra');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['codigo_entrada']);
            $table->dropIndex(['canal']);
            $table->dropColumn(['canal', 'codigo_entrada', 'verificado', 'verificado_at', 'verificado_user_id']);
        });
    }
};
