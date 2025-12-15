<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CursoCreado;
use App\Listeners\NotificarUsuariosCursoCreado;
use App\Listeners\RegistrarCursoEnLog;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Los eventos y sus listeners.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
        ],
        // Aquí se agregan tus eventos y listeners personalizados:
        CursoCreado::class => [
            NotificarUsuariosCursoCreado::class,
            RegistrarCursoEnLog::class,
        ],
    ];

    /**
     * Registra cualquier evento y listener.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Registrar otros eventos si es necesario
    }
}
