<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller{
    public function eventos(){
        return Evento::where('estado','ACTIVO')->get();
    }
}
