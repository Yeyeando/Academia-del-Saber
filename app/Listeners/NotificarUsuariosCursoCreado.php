<?php
namespace App\Listeners;

use App\Events\CursoCreado;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificarUsuariosCursoCreado implements ShouldQueue
{
    public function handle(CursoCreado $event): void
    {
        $curso = $event->curso;

        $usuarios = User::where('role', 'admin')->get();

        foreach ($usuarios as $usuario) {
            Mail::raw(
                "Hola {$usuario->name}, nuevo: {$curso->nombre} ({$curso->precio}€)",
                function ($message) use ($usuario) {
                    $message->to($usuario->email)
                            ->subject('Nuevo Curso - Academia del Saber');
                }
            );
        }
    }
}