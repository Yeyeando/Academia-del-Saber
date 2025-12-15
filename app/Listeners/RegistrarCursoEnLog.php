<?php
namespace App\Listeners;

use App\Events\CursoCreado;
use Illuminate\Support\Facades\Log;

class RegistrarCursoEnLog
{
    public function handle(CursoCreado $event): void
    {
        $curso = $event->curso;
        
        Log::info('Curso creado:', [
            'id' => $curso->id,
            'nombre' => $curso->nombre,
            'precio' => $curso->precio,
            'vacantes' => $curso->vacantes,
            'fecha_inicio' => $curso->fecha_inicio,
            'fecha_fin' => $curso->fecha_fin,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
}
