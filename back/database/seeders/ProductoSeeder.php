<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('productos')->insert([
            [
                'nombre'=>"COMBO 1",
                'precio'=>"20",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"1",
            ],
            [
                'nombre'=>"COMBO 2",
                'precio'=>"30",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"1",
            ],
            [
                'nombre'=>"COMBO 3",
                'precio'=>"35",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"1",
            ],
            [
                'nombre'=>"COMBO NACHO",
                'precio'=>"25",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"1",
            ],
        ]);
    }
}
