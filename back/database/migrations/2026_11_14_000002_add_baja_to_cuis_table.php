<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cuis', function (Blueprint $table) {
            $table->string('motivoBaja')->nullable();
            $table->unsignedBigInteger('userBaja_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cuis', function (Blueprint $table) {
            $table->dropColumn(['motivoBaja', 'userBaja_id']);
        });
    }
};
