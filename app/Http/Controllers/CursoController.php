<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCursoRequest;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Curso;
use App\Models\User;
use App\Notifications\NuevoCursoNotificacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Exports\CursosExport;
use App\Events\CursoCreado;

class CursoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $texto = $request->get('busqueda');
        $categoria = $request->get('categoria_id');
        $pocas = $request->get('stock_bajo');

        // Clave de caché única según filtros
        $cacheKey = "cursos_page_{$page}_txt_{$texto}_cat_{$categoria}_pocas_{$pocas}";

        $cursos = Cache::remember($cacheKey, 3600, function () use ($page, $texto, $categoria, $pocas) {
            return Curso::query()
                ->buscar($texto)
                ->porCategoria($categoria)
                ->when($pocas, fn($q) => $q->pocasVacantes())
                ->orderBy('id', 'desc')
                ->paginate(10, ['*'], 'page', $page);
        });

        $categorias = Categoria::orderBy('nombre')->get();

        return view('cursos.index', [
            'cursos' => $cursos,
            'categorias' => $categorias
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

        event(new CursoCreado($curso));

        Cache::flush();

        $admin = User::find(1);
        if ($admin) {
            $admin->notify(new NuevoCursoNotificacion($curso));
        }


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
        $curso = Curso::findOrFail($id);
        $this->authorize('update', $curso);

        $categorias = Categoria::orderBy('nombre')->get();

        return view('cursos.edit', compact('curso', 'categorias'));
    }


    public function update(Request $request, string $id)
    {
        $curso = Curso::findOrFail($id);
        $this->authorize('update', $curso);

        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'vacantes' => 'required|integer|min:1',
            'categoria_id' => 'required|exists:categorias,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'foto' => 'nullable|image',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('cursos', 'public');
        }

        $curso->update($data);

        Cache::flush();

        return redirect()->route('cursos.index')
            ->with('status', 'Curso actualizado correctamente');
    }


    public function destroy(string $id)
    {
        $curso = Curso::findOrFail($id);
        $this->authorize('delete', $curso);

        $curso->delete();

        Cache::flush();

        return redirect()->route('cursos.index')
            ->with('status', 'Curso eliminado');
    }

    public function exportPdf()
    {
        $cursos = Curso::all();
        $pdf = Pdf::loadView('cursos.pdf', ['cursos' => $cursos]);
        return $pdf->download('catalogo.pdf');
    }

    public function exportExcel()
    {
        // Llamar a la función export() de la clase CursosExport
        return (new CursosExport())->export();
    }


}
