<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Curso;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CursoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $page = $request->get('page', 1);

        $cursos = Cache::remember("cursos_page_{$page}", 3600, function () use ($page) {
            return Curso::orderBy('id', 'desc')->paginate(15, ['*'], 'page', $page);
        });

        $cursoClass = Curso::class;

        return view('cursos.index', [
            'cursos' => $cursos,
            'cursoClass' => $cursoClass,  
        ]);
    }


    public function create()
    {
        $this->authorize('create', Curso::class);
        return view('cursos.create');
    }

    public function store(StoreCursoRequest $request)
    {
        $this->authorize('create', Curso::class);

        $data = $request->validated();

        if ($request->hasFile('foto')) {
            $rutaFoto = $request->file('foto')->store('cursos', 'public');
            $data['foto'] = $rutaFoto;
        }

        $curso = Curso::create($data);

        Cache::flush();

        return redirect('/cursos')
            ->with('status', 'Curso creado: ' . $curso->nombre);
    }

    public function show($id)
    {
        $curso = Curso::find($id);

        if (!$curso) {
            Log::warning('Intento de acceso a curso inexistente', ['id' => $id]);
            abort(404);
        }

        return view('cursos.show', compact('curso'));
    }

    public function edit(string $id)
    {
        $this->authorize('update', Curso::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $this->authorize('update', Curso::findOrFail($id));
        Cache::flush();
    }

    public function destroy(string $id)
    {
        $this->authorize('delete', Curso::findOrFail($id));
        Cache::flush();
    }
}
