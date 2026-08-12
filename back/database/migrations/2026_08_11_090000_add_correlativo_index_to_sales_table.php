<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El correlativo de factura se calcula con MAX(numeroFactura) por cufd y tipo
 * dentro del lock de la venta. Con este indice la consulta es una lectura
 * directa del arbol y el lock se libera de inmediato.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['cufd', 'tipo', 'numeroFactura'], 'sales_correlativo_idx');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_correlativo_idx');
        });
    }
};
