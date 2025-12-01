<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function agregar(Curso $curso)
    {
        $carrito = session()->get('carrito', []);

        // Si el curso ya está en el carrito, no se añade de nuevo
        if (isset($carrito[$curso->id])) {
            return redirect()->back()->with('info', 'Este curso ya está en tu carrito.');
        }

        $carrito[$curso->id] = [
            "nombre" => $curso->nombre,
            "precio" => $curso->precio,
            "imagen" => $curso->foto
        ];

        session()->put('carrito', $carrito);

        return redirect()->route('cursos.index')
            ->with('success', 'Curso agregado al carrito!');


    }

    public function ver()
    {
        $carrito = session()->get('carrito', []);
        return view('carrito.ver', compact('carrito'));
    }

    public function eliminar($id)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session()->put('carrito', $carrito);
        }

        return redirect()->route('carrito.ver')->with('success', 'Curso eliminado del carrito.');
    }

    public function vaciar()
    {
        session()->forget('carrito');

        return redirect()->route('carrito.ver')->with('success', 'Carrito vaciado.');
    }
}
