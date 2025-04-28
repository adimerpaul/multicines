<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Programa;
use Illuminate\Http\Request;

class WebController extends Controller{
    function movie($id){
        $hoy = date('Y-m-d');
        $hoyDatetime = date('Y-m-d H:i:s');
        $movie = Movie::where('id', $id)
            ->with(['programas' => function ($query) use ($hoyDatetime, $hoy) {
                $query->where('fecha', '>=', date('Y-m-d'))
                    ->where('activo', 'ACTIVO')
//                    ->where('fecha', $hoy)
                    ->where('horaInicio', '>=', $hoyDatetime)
                    ->orderBy('fecha')
                    ->orderBy('horaInicio');
            }])
            ->first();

        $ofertas = [
            [
                "id" => 1,
                "nombre" => "Miercoles 2x1",
                "descripcion" => "2x1 en todas las funciones",
                "imagen" => "offer01.png",
            ],
            [
                "id" => 2,
                "nombre" => "Martes duo",
                "descripcion" => "Martes cada funcion con pipocas gratis",
                "imagen" => "offer02.png",
            ],
            [
                "id" => 3,
                "nombre" => "Jueves de Estreno",
                "descripcion" => "Estrenos en todas las funciones",
                "imagen" => "offer03.png",
            ],
        ];

        if ($movie) {
            // Agrupar programas por fecha
            $programasAgrupados = [];

            foreach ($movie->programas as $programa) {
                $fechaFormateada = date('Y-m-d', strtotime($programa->fecha)); // Asegurarse de que esté como '2025-04-28'
                if (!isset($programasAgrupados[$fechaFormateada])) {
                    $programasAgrupados[$fechaFormateada] = [];
                }
                $programasAgrupados[$fechaFormateada][] = [
                    "id" => $programa->id,
                    "horaInicio" => $programa->horaInicio,
                    "horaFin" => $programa->horaFin,
                    "subtitulada" => $programa->subtitulada,
                    "sala_id" => $programa->sala_id,
                    "nroFuncion" => $programa->nroFuncion,
                ];
            }

            $movie->programaActivas = $programasAgrupados;

            unset($movie->programas);

            return [
                "movie" => $movie,
                "ofertas" => $ofertas,
            ];
        } else {
            return response()->json(['error' => 'Programa no encontrado'], 404);
        }
    }

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
