<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cuis')->insert([
            [
                'codigo' => 'A15EF75C',
                'fechaVigencia' => '2023-07-26 19:24:41',
                'fechaCreacion' => '2022-07-26 19:24:41',
                'codigoPuntoVenta' => 0,
                'codigoSucursal' => 0,
            ],
            [
                'codigo' => 'F727E9CB',
                'fechaVigencia' => '2023-07-26 19:25:12',
                'fechaCreacion' => '2022-07-26 19:24:41',
                'codigoPuntoVenta' => 1,
                'codigoSucursal' => 0,
            ],
        ]);
    }
}
