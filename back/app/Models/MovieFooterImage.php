<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieFooterImage extends Model
{
    use HasFactory;
    protected $fillable=[
        'movie_id',
        'imagen',
        'nombre',
        'orden',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
