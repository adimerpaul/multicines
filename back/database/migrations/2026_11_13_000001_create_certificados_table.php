<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->string('archivo')->nullable();
            $table->string('titular')->nullable();
            $table->string('organizacion')->nullable();
            $table->string('cargo')->nullable();
            $table->string('nit')->nullable();
            $table->string('email')->nullable();
            $table->string('emisor')->nullable();
            $table->string('serial')->nullable();
            $table->dateTime('fechaInicio')->nullable();
            $table->dateTime('fechaFin')->nullable();
            $table->longText('publicKey');      // certificado X.509 en PEM
            $table->longText('privateKey');     // llave privada PEM cifrada con APP_KEY
            $table->string('p12Path')->nullable();
            $table->boolean('activo')->default(false);
            $table->dateTime('fechaActivacion')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('permisos')->insertOrIgnore([
            ['id' => 32, 'nombre' => 'Certificado Digital', 'permiso_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('certificados');
        DB::table('permisos')->where('id', 32)->delete();
    }
};
