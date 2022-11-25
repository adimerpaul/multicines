<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('permisos')->insert([
            ['nombre'=>'Usuarios'],
            ['nombre'=>'Cuis'],
            ['nombre'=>'Sincronizacion'],
            ['nombre'=>'Cufd'],
            ['nombre'=>'Evento Significativo'],
            ['nombre'=>'Peliculas'],
            ['nombre'=>'Distribuidor'],
            ['nombre'=>'Salas'],
            ['nombre'=>'Tarifas'],
            ['nombre'=>'Rubro'],
            ['nombre'=>'Producto'],
            ['nombre'=>'Programacion'],
            ['nombre'=>'Venta Boleteria'],
            ['nombre'=>'Listado Boleteria'],
            ['nombre'=>'Venta Candy'],
            ['nombre'=>'Listado Candy'],
            ['nombre'=>'Caja Boleteria'],
            ['nombre'=>'Caja Candy'],
            ['nombre'=>'Alquiler Ambiente'],
            ['nombre'=>'Clientes'],
            ['nombre'=>'Cortesia'],
        ]);
    }
}
