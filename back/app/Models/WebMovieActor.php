<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebMovieActor extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['web_movie_id', 'nombre', 'imagen'];

    protected $hidden = ['created_at', 'updated_at'];

    public function webMovie()
    {
        return $this->belongsTo(WebMovie::class);
    }
}

