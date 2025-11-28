<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Curso;

class CursoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cursos = Curso::all();
        return view('cursos.index', ['cursos' => $cursos]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cursos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCursoRequest $request)
    {
        $data = $request->validated();

        // Si el usuario subió una foto, la guardamos
        if ($request->hasFile('foto')) {
            $rutaFoto = $request->file('foto')->store('cursos', 'public');
            $data['foto'] = $rutaFoto;
        }

        // Crear el curso en la base de datos
        $curso = Curso::create($data);

        // Redirige a URL /cursos y añade sesión con mensaje "Curso creado: nombre curso"
        return redirect('/cursos')
            ->with('status', 'Curso creado: ' . $curso->nombre);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $curso = Curso::find($id);
        //Comprobar que
        if (!$curso) {
            Log::warning('Intento de acceso a curso inexistente', [
                'id' => $id,
            ]);
            abort(404);
        }

        Log::info('Ficha de curso visitada', [
            'id' => $id,
            'nombre' => $curso->nombre,
        ]);

        return view('cursos.show', compact('curso'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function buscar($texto = null)
    {
        if ($texto) {
            $cursos = Curso::where('nombre', 'like', "%$texto%")->get();
        } else {
            $cursos = Curso::all();
        }

        return view('cursos.index', ['cursos' => $cursos, 'texto' => $texto]);
    }
}