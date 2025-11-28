<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TiendaAbierta
{
    public function handle(Request $request, Closure $next)
    {
        // Interruptor de la tienda: true = abierta, false = cerrada
        $tiendaAbierta = true;

        // Si la tienda está cerrada, mostramos un mensaje y no dejamos pasar
        if (!$tiendaAbierta) {
            return response(
                'No estás invitado a nuestros cursos',
                503
            );
        }

        // Si la tienda está abierta, continuamos hacia la ruta/controlador
        return $next($request);
    }
}
