<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("programas")->insert([
            [
                'fecha'=>"2022-08-18",
                "horaInicio"=>"2022-08-18 10:00:00",
                "horaFin"=>"2022-08-18 12:00:00",
                "subtitulada"=>"NO",
                "nroFuncion"=>"1",
                "user_id"=>"1",
                "movie_id"=>"1",
                "sala_id"=>"3",
                "price_id"=>"1",
            ],
            [
                'fecha'=>"2022-08-18",
                "horaInicio"=>"2022-08-18 13:00:00",
                "horaFin"=>"2022-08-18 15:00:00",
                "subtitulada"=>"NO",
                "nroFuncion"=>"1",
                "user_id"=>"1",
                "movie_id"=>"1",
                "sala_id"=>"1",
                "price_id"=>"1",
            ],
            [
                'fecha'=>"2022-08-18",
                "horaInicio"=>"2022-08-18 10:00:00",
                "horaFin"=>"2022-08-18 12:00:00",
                "subtitulada"=>"NO",
                "nroFuncion"=>"1",
                "user_id"=>"1",
                "movie_id"=>"2",
                "sala_id"=>"2",
                "price_id"=>"1",
            ],
            [
                'fecha'=>"2022-08-18",
                "horaInicio"=>"2022-08-18 13:00:00",
                "horaFin"=>"2022-08-18 15:00:00",
                "subtitulada"=>"NO",
                "nroFuncion"=>"1",
                "user_id"=>"1",
                "movie_id"=>"2",
                "sala_id"=>"1",
                "price_id"=>"1",
            ],
        ]);
    }
}
