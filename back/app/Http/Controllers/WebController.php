<?php

namespace App\Http\Controllers;

use App\Models\Programa;
use Illuminate\Http\Request;

class WebController extends Controller{
    public function movies(){
        $hoy = date('Y-m-d');

        $programas = Programa::with('movie')
            ->where('fecha', $hoy)
            ->where('activo', 'ACTIVO')
            ->get();

        $peliculasUnicas = $programas->unique('movie_id')->map(function ($programa) {
            return [
                'id' => $programa->movie->id,
                'nombre' => $programa->movie->nombre,
                'formato' => $programa->movie->formato,
                'imagen' => $programa->movie->imagen,
                'clasificacion' => $programa->movie->clasificacion,
                'ratingPublic' => $programa->movie->ratingPublic,
                'ratingCritica' => $programa->movie->ratingCritica,
            ];
        })->values();

        return response()->json($peliculasUnicas);
    }
}
