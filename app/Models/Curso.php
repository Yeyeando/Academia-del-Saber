<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $fillable = ['nombre', 'precio', 'vacantes','foto', 'fecha_inicio', 'fecha_fin'];
}
