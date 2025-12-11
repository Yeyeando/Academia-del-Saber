<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CarritoController;
use Symfony\Component\HttpFoundation\Request;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Página principal
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Listado de cursos
Route::get('/cursos', [CursoController::class, 'index'])
    ->name('cursos.index');

// ⚠️ RUTAS CON TEXTO FIJO SIEMPRE ANTES QUE LAS DINÁMICAS
// Ver un curso concreto
Route::get('/cursos/{curso}', [CursoController::class, 'show'])
    ->whereNumber('curso')
    ->name('cursos.show');
    
Route::get('/cursos/export-pdf', [CursoController::class, 'exportPdf'])
    ->name('cursos.export.pdf');

Route::get('/cursos/export-excel', [CursoController::class, 'exportExcel'])
    ->name('cursos.export.excel');
    
/*
|--------------------------------------------------------------------------
| Rutas Protegidas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Crear curso
    Route::get('/cursos/create', [CursoController::class, 'create'])
        ->name('cursos.create');

    // Guardar curso
    Route::post('/cursos', [CursoController::class, 'store'])
        ->name('cursos.store');

     // Carrito
    Route::post('/carrito/agregar/{curso}', [CarritoController::class, 'agregar'])
        ->name('carrito.agregar');

    Route::get('/carrito', [CarritoController::class, 'ver'])
        ->name('carrito.ver');

    Route::delete('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])
        ->name('carrito.eliminar');

    Route::post('/carrito/vaciar', [CarritoController::class, 'vaciar'])
        ->name('carrito.vaciar');

    // Editar curso
    Route::get('/cursos/{curso}/edit', [CursoController::class, 'edit'])
        ->name('cursos.edit');

    // Actualizar curso
    Route::put('/cursos/{curso}', [CursoController::class, 'update'])
        ->name('cursos.update');

    // Eliminar curso
    Route::delete('/cursos/{curso}', [CursoController::class, 'destroy'])
        ->name('cursos.destroy');

    /*
    |--------------------------------------------------------------------------
    | Perfil (Breeze)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Dashboard (Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])
    ->get('/dashboard', fn() => view('dashboard'))
    ->name('dashboard');

Route::get('/idioma/{lang}', function (Request $request, $lang) {

    if (!in_array($lang, ['es', 'en'])) {
        abort(400);
    }

    // Guardar en la sesión
    session(['locale' => $lang]);

    // Crear cookie NO cifrada (importante para bootstrap/app.php)
    setcookie('locale', $lang, time() + 60 * 60 * 24 * 365, '/');

    // Volver a la página anterior
    return back();

})->name('cambiar.idioma');


require __DIR__.'/auth.php';
