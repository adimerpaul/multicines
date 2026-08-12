<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::table('permisos')->insertOrIgnore([
            ['id' => 31, 'nombre' => 'Vincular Pago', 'permiso_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        DB::table('permisos')->where('id', 31)->delete();
    }
};
