<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name', 'Academia del saber') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans antialiased">

<nav class="bg-gray-100 p-4">
    <h1 class="text-xl font-bold inline-block mr-6">Academia del saber</h1>
    
    <!--Cambiar idioma-->
    <a href="{{ url('/') }}" class="mr-4">{{ __('messages.home') }}</a>
    <a href="{{ route('cursos.index') }}" class="mr-4">{{ __('messages.courses') }}</a>
    
    @can('create', \App\Models\Curso::class)
        <a href="{{ route('cursos.create') }}" class="mr-4">Crear</a>
    @endcan

    <form method="POST" action="{{ route('logout') }}" id="logout-form">
    @csrf
    <button type="submit">Logout</button>
    </form>

    


    <!-- Selector de idioma -->
    <span class="ml-4">
        Idioma:
        <a href="{{ route('cambiar.idioma', 'es') }}">ES</a> |
        <a href="{{ route('cambiar.idioma', 'en') }}">EN</a>
    </span>
</nav>

<!-- Esto reemplaza a $slot (vistas Breeze) -->
<main class="p-6">
    {{ $slot ?? '' }}
</main>

<footer class="text-center py-4 text-sm text-gray-600">
    &copy; {{ date('Y') }} Academia del saber
</footer>

</body>
</html>
