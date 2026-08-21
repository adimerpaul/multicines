<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Una butaca de una funcion solo puede estar reservada una vez.
 *
 * Sin este indice, dos peticiones simultaneas del mismo cajero (doble clic o
 * reintento de red) creaban dos momentaneos de la misma butaca y la venta
 * terminaba imprimiendo dos boletos identicos.
 */
return new class extends Migration
{
    public function up()
    {
        // Limpiar duplicados previos: se conserva la reserva mas antigua.
        DB::statement("
            DELETE m1 FROM momentaneos m1
            INNER JOIN momentaneos m2
                ON m1.programa_id = m2.programa_id
               AND m1.fila = m2.fila
               AND m1.columna = m2.columna
               AND m1.letra = m2.letra
               AND m1.id > m2.id");

        Schema::table('momentaneos', function (Blueprint $table) {
            $table->unique(['programa_id', 'fila', 'columna', 'letra'], 'momentaneos_butaca_unique');
        });
    }

    public function down()
    {
        Schema::table('momentaneos', function (Blueprint $table) {
            $table->dropUnique('momentaneos_butaca_unique');
        });
    }
};
