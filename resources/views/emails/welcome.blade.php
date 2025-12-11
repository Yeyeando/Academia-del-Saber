@component('mail::message')
# ¡Hola {{ $userName }}!

Bienvenido a **Academia del Saber**.

@component('mail::button', ['url' => config('app.url')])
Ver Cursos
@endcomponent

Gracias, {{ config('app.name') }}
@endcomponent
