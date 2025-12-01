<x-app-layout>
    <div class="max-w-7xl mx-auto py-8">

        <h1 class="text-3xl font-bold mb-6">🛒 Mi Carrito de Cursos</h1>

        @if(empty($carrito))
            <div class="bg-white p-10 rounded-lg shadow text-center">
                <h2 class="text-xl font-semibold mb-4">Tu carrito está vacío</h2>
                <a href="{{ route('cursos.index') }}" class="text-blue-600 font-semibold">
                    Ver cursos
                </a>
            </div>
        @else
            <div class="bg-white p-6 rounded-lg shadow">

                <table class="w-full">
                    <thead>
                        <tr class="bg-blue-500 text-white">
                            <th class="p-3">Curso</th>
                            <th class="p-3">Precio</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $total = 0; @endphp

                        @foreach($carrito as $id => $item)
                            @php $total += $item['precio']; @endphp
                        
                            <tr class="border-b">
                                @if (!empty($item['imagen']))
                                    <td class="p-3 flex gap-4 items-center">
                                        <img src="{{ asset('storage/' . $item['imagen']) }}" 
                                            alt="Imagen del curso" class="w-20 h-20 rounded object-cover">

                                        <span>{{ $item['nombre'] }}</span>
                                    </td>
                                @else
                                    <td class="p-3 flex gap-4 items-center">
                                        <img src={{ asset('storage/cursos/cip_centro_de_formacion_logo.jpg') }}
                                            alt="Logo cip" class="w-20 h-20 rounded object-cover">

                                        <span>{{ $item['nombre'] }}</span>
                                    </td>
                                @endif

                                <td class="p-3">
                                    {{ number_format($item['precio'], 2) }} €
                                </td>

                                <td class="p-3">
                                    <form action="{{ route('carrito.eliminar', $id) }}" 
                                          method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 text-right text-2xl font-bold">
                    Total: {{ number_format($total, 2) }} €
                </div>

                <form action="{{ route('carrito.vaciar') }}" method="POST" class="mt-4">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded">
                        🗑️ Vaciar Carrito
                    </button>
                </form>

                <a href="{{ route('cursos.index') }}" class="mt-4 inline-block text-blue-600">
                    ← Seguir explorando cursos
                </a>

            </div>
        @endif
    </div>
</x-app-layout>
