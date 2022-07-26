<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('movies')->insert([
            'nombre' => 'Movie 1',
            'duracion' => 100,
            'paisOrigen' => 'Pais 1',
            'genero' => 'Genero 1',
            'sipnosis' => 'Sipnosis 1',
            'urlTrailer' => 'Url 1',
            'imagen' => 'Imagen 1',
            'clasificacion' => 'Clasificacion 1',
            'fechaEstreno' => now(),
            'distributor_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
