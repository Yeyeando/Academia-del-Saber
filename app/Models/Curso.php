<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Categoria;

class Curso extends Model
{
    protected $fillable = ['nombre', 'precio', 'vacantes','foto', 'fecha_inicio', 'fecha_fin', 'categoria_id'];

    public function categoria() {
        return $this->belongsTo(Categoria::class);
    }

}

