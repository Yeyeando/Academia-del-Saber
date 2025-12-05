<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;  

class Curso extends Model
{
    use HasFactory;  
    
    protected $fillable = ['nombre', 'precio', 'vacantes','foto', 'fecha_inicio', 'fecha_fin', 'categoria_id'];

    /**
     * Scope para buscar productos por nombre
     * Uso: Producto::buscar('manzana')->get()
     */
    public function scopeBuscar($query, $texto)
    {
        if ($texto) {
            return $query->where('nombre', 'LIKE', '%' . $texto . '%');
        }
        return $query;
    }

    /**
     * Scope para filtrar productos por categoría
     * Uso: Producto::porCategoria(1)->get()
     */
    public function scopePorCategoria($query, $categoria_id)
    {
        if ($categoria_id) {
            return $query->where('categoria_id', $categoria_id);
        }
        return $query;
    }

    /**
     * Scope para filtrar cursos con pocas vacantes
     * Uso: Producto::stockBajo()->get()
     */
    public function scopePocasVacantes($query)
    {
        return $query->where('vacantes', '<', 10);
    }

    public function categoria() {
        return $this->belongsTo(Categoria::class);
    }
}

