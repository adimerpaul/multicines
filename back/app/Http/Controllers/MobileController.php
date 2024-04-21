<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileController extends Controller{
    public function movies(){
        $date = date('Y-m-d');
        return DB::select("
        select m.id,m.nombre,m.duracion,m.formato,m.imagen,(
        SELECT count(*) FROM tickets WHERE programa_id=p.id and devuelto=0
        ) as cantidad
        from programas p
        INNER JOIN movies m ON p.movie_id=m.id
        where p.fecha='$date'
        and p.activo='ACTIVO'
        GROUP by m.id,m.nombre,m.duracion,m.formato,m.imagen;");
    }
    public function eventos(){
        return Evento::where('estado','ACTIVO')->get();
    }
}
