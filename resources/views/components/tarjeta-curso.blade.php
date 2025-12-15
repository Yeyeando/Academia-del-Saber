<div class="w-full h-full p-4 border border-gray-300 rounded-lg flex flex-col">

    {{-- Imagen --}}
    @if($curso->foto)
        <img src="{{ asset('storage/' . $curso->foto) }}" 
             alt="{{ $curso->nombre }}" 
             class="max-w-full h-auto mb-4 p-16">
     @else
        <img src="{{ asset('storage/cursos/cip_centro_de_formacion_logo.jpg') }}" 
             alt="Imagen por defecto" 
             class="max-w-full h-auto mb-4 p-16">
    @endif

    <h3 class="text-lg font-semibold">{{ $curso->nombre }}</h3>

    <p>
        <strong>{{ __('messages.price') }}</strong>
        {{ number_format($curso->precio, 2) }} €
    </p>

    @if($mostrarVacantes)
        <p><strong>{{ __('messages.vacantes') }}:</strong> {{ $curso->vacantes }}</p>
        <p><strong>{{ __('messages.start_date') }}:</strong> {{ $curso->fecha_inicio }}</p>
        <p><strong>{{ __('messages.end_date') }}:</strong> {{ $curso->fecha_fin }}</p>

        @if($curso->categoria)
            <p><strong>{{ __('messages.category') }}:</strong> {{ $curso->categoria->nombre }}</p>
        @else
            <p>Sin categoría</p>
        @endif
    @endif

    <a href="{{ route('cursos.show', $curso->id) }}"
       class="bg-blue-800 hover:bg-blue-900 text-white px-4 py-2 rounded  w-1/2">
        {{ __('messages.see_details') }}
    </a>

    {{-- Carrito --}}
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

    {{-- Empuja los botones abajo --}}
    <div class="flex-grow"></div>

    {{-- Botones admin --}}
    <div class="flex mt-4 gap-2">
        @can('update', $curso)
            <a href="{{ route('cursos.edit', $curso->id) }}" 
               class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded w-full text-center">
                ✏️ Editar
            </a>
        @endcan

        @can('delete', $curso)
            <form action="{{ route('cursos.destroy', $curso->id) }}" method="POST" class="w-full">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded w-full"
                        onclick="return confirm('¿Seguro que quieres eliminar este curso?')">
                    🗑 Eliminar
                </button>
            </form>
        @endcan
    </div>

</div>
