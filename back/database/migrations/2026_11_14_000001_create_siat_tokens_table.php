<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('siat_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('token');               // token delegado del SIAT (JWT)
            $table->string('descripcion')->nullable();
            $table->string('email')->nullable();  // "sub" del JWT
            $table->string('nit')->nullable();    // "nitDelegado" del JWT
            $table->string('subsistema')->nullable();
            $table->dateTime('fechaEmision')->nullable();   // "iat"
            $table->dateTime('fechaVigencia')->nullable();  // "exp"
            $table->boolean('activo')->default(false);
            $table->dateTime('fechaActivacion')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('permisos')->insertOrIgnore([
            ['id' => 33, 'nombre' => 'Token SIAT', 'permiso_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Pasar a la BD el token que hoy esta en el .env, para que siga funcionando igual
        $actual = env('TOKEN');
        if ($actual) {
            $data = \App\Models\SiatToken::datosDesdeJwt($actual);
            DB::table('siat_tokens')->insert(array_merge($data, [
                'token' => $actual,
                'descripcion' => 'Importado del .env',
                'activo' => true,
                'fechaActivacion' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('siat_tokens');
        DB::table('permisos')->where('id', 33)->delete();
    }
};
