<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebStudio extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = ['nombre'];

    protected $hidden = ['created_at', 'updated_at'];

    public function webMovies()
    {
        return $this->hasMany(WebMovie::class);
    }
}

