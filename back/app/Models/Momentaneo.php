<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Momentaneo extends Model
{
    use HasFactory;
    protected $fillable=[
        "fila",
        "columna",
        "letra",
        "user_id",
        "fecha",
        "precio",
        "pelicula",
        "promo",
        "pelicula_id",
        "user_id",
        "programa_id",
    ];

    /**
     * Butaca reservada junto con los datos de su funcion (sala, nro de funcion
     * y hora de inicio). El panel de ventas los muestra al cajero para que vea
     * a que funcion pertenece cada butaca antes de facturar.
     */
    public function scopeConFuncion($query)
    {
        return $query
            ->leftJoin('programas', 'programas.id', '=', 'momentaneos.programa_id')
            ->leftJoin('salas', 'salas.id', '=', 'programas.sala_id')
            ->select([
                'momentaneos.id',
                'momentaneos.programa_id',
                'momentaneos.pelicula',
                'momentaneos.pelicula_id',
                'momentaneos.fecha',
                'momentaneos.precio',
                'momentaneos.promo',
                'momentaneos.fila',
                'momentaneos.columna',
                'momentaneos.letra',
                'salas.nombre as sala',
                'programas.nroFuncion as nroFuncion',
                'programas.horaInicio as horaInicio',
            ]);
    }
}
