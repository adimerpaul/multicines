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
            [
                'nombre'=>"SODA",
                'precio'=>"9",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"2",
            ],
            [
                'nombre'=>"AQUARIUS",
                'precio'=>"9",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"2",
            ],            [
                'nombre'=>"AGUA 500ML",
                'precio'=>"8",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"2",
            ],            [
                'nombre'=>"CAFE",
                'precio'=>"3",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"2",
            ],
            [
                'nombre'=>"PEPSI 7UP GUARANA H2O",
                'precio'=>"9",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"2",
            ],

            [
                'nombre'=>"GRANIZADO",
                'precio'=>"10",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"3",
            ],

            [
                'nombre'=>"CAJA NACHOS",
                'precio'=>"17",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"4",
            ],
            [
                'nombre'=>"PIPOCA PEQUEÑA",
                'precio'=>"12",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"4",
            ],
            [
                'nombre'=>"PIPOCA MEDIANA",
                'precio'=>"16",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"4",
            ],
            [
                'nombre'=>"PIPOCA GRANDE",
                'precio'=>"20",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"4",
            ],
            [
                'nombre'=>"MINI COMBO",
                'precio'=>"10",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"4",
            ],
            [
                'nombre'=>"PORCION QUESO",
                'precio'=>"5",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"4",
            ],

            [
                'nombre'=>"HOT DOG",
                'precio'=>"10",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"5",
            ],
            [
                'nombre'=>"HOT DOG (PROMO)",
                'precio'=>"9",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"5",
            ],

            [
                'nombre'=>"MYM",
                'precio'=>"12",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"6",
            ],
            [
                'nombre'=>"SNICKERS",
                'precio'=>"12",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"6",
            ],
            [
                'nombre'=>"SKTTLES",
                'precio'=>"12",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"6",
            ],
            [
                'nombre'=>"MYM MINI",
                'precio'=>"5",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"6",
            ],
            [
                'nombre'=>"GOMITAS",
                'precio'=>"5",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"6",
            ],

            [
                'nombre'=>"RED BULL",
                'precio'=>"5",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"7",
            ],
            [
                'nombre'=>"GATORADE",
                'precio'=>"12",
                'imagen'=>"rubro.jpg",
                'color'=>"#770050",
                "rubro_id"=>"7",
            ],

        ]);
    }
}
