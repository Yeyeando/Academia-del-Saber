<x-app-layout>
    <h1>{{ __('messages.view_detail') }}</h1>
    
    @if ($curso->foto)
        <img src="{{ asset('storage/' . $curso->foto) }}" 
             alt="{{ $curso->nombre }}" 
             style="max-width: 300px; height: auto;">
    @else
        <p>{{ __('messages.no_photo') }}</p>
    @endif
    
    <h2>{{ $curso->nombre }}</h2>
    
    <p>{{ __('messages.price') }}: {{ $curso->precio }} €</p>
    <p>{{ __('messages.vacantes') }}: {{ $curso->vacantes }}</p>
    <p>{{ __('messages.start_date') }}: {{ \Carbon\Carbon::parse($curso->fecha_inicio)->format('d/m/Y') }}</p>
    <p>{{ __('messages.end_date') }}: {{ \Carbon\Carbon::parse($curso->fecha_fin)->format('d/m/Y') }}</p>

    @auth
        @php
            $carrito = session('carrito', []);
            $enCarrito = isset($carrito[$curso->id]);
        @endphp

        @if($enCarrito)
            <p class="text-green-600 font-bold mt-4">
                ✔ Este curso ya está en tu carrito
            </p>
        @else
            <form action="{{ route('carrito.agregar', $curso->id) }}" method="POST" class="mt-4">
                @csrf
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    🛒 Añadir al carrito
                </button>
            </form>
        @endif
    @endauth
    
    <a href="{{ route('cursos.index') }}">{{ __('messages.back_to_list') }}</a>
    
</x-app-layout>
