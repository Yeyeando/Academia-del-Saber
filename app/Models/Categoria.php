<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function cursos()
    {
        	return $this->hasMany(Curso::class);
    }

}
