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
        if (!Schema::hasColumn('web_movie_programa', 'venta_online')) {
            Schema::table('web_movie_programa', function (Blueprint $table) {
                $table->boolean('venta_online')->default(true)->after('programa_id');
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
        if (Schema::hasColumn('web_movie_programa', 'venta_online')) {
            Schema::table('web_movie_programa', function (Blueprint $table) {
                $table->dropColumn('venta_online');
            });
        }
    }
};
