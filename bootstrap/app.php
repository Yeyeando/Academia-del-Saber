<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

// Detectar cookie 'locale' enviada por el navegador y fijarla como variable de entorno
// para que env('APP_LOCALE') devuelva el idioma deseado durante el bootstrap.
if (isset($_COOKIE['locale']) && in_array($_COOKIE['locale'], ['es', 'en'])) {
    $detectedLocale = $_COOKIE['locale'];
    // Setear para env() / config() antes de que se cargue la configuración
    putenv("APP_LOCALE={$detectedLocale}");
    $_ENV['APP_LOCALE'] = $detectedLocale;
    $_SERVER['APP_LOCALE'] = $detectedLocale;
}
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    // Alias de middlewares
        $middleware->alias([
            'tienda.abierta' => \App\Http\Middleware\TiendaAbierta::class,
            'set.locale' => \App\Http\Middleware\SetLocale::class,
    ]);

    // Middleware global (SetLocale en todas partes)
        $middleware->prepend([
            \App\Http\Middleware\SetLocale::class,
        ]);
    })


    ->withExceptions(function (Exceptions $exceptions) {
    // Manejar errores 404 con una vista personalizada
            $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
                Log::error('Excepción capturada en EcoMarket', [
                'mensaje' => $e->getMessage(),
                'tipo' => get_class($e),
            ]);
            return response()->view('errors.tienda-no-encontrada', [], 404);
        });
    })->create();

