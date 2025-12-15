<x-app-layout>

    <h2>{{ __('messages.course_list') }}</</h2>

    <x-tarjeta-destacada>
        <x-slot name="titulo">
            Bienvenido a la Academia del Saber
        </x-slot>

        <p>¿Listo para mejorar tus capacidades? Cada uno de nuestros cursos te enseñaran que 
            no hay nada imposible con una buena base, y aquí la impartimos de la mejor manera.
        </p>
        <p><strong>!Hora de crecer!</strong></p>
    </x-tarjeta-destacada>

    
        {{-- Ejemplo de uso de alertas --}}
    <x-alerta tipo="exito">
        ✓ Los cursos se han cargado correctamente.
    </x-alerta>
    {{-- Formulario de búsqueda y filtros --}}
    <div style="background: #f0f0f0; padding: 20px; margin-bottom: 20px; border-radius: 5px;">
        <h3>🔍 Buscar y Filtrar Cursos</h3>
        
        <form method="GET" action="{{ route('cursos.index') }}">
            <div style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap;">
                
                {{-- Campo de búsqueda por nombre --}}
                <div>
                    <label for="busqueda">Buscar por nombre:</label><br>
                    <input type="text" 
                           id="busqueda" 
                           name="busqueda" 
                           value="{{ request('busqueda') }}"
                           placeholder="Ej: Manzanas"
                           style="padding: 8px; width: 200px;">
                </div>
                
                {{-- Filtro por categoría --}}
                <div>
                    <label for="categoria_id">Categoría:</label><br>
                    <select name="categoria_id" id="categoria_id" style="padding: 8px; width: 200px;">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" 
                                    {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                {{-- Checkbox para stock bajo --}}
                <div>
                    <label>
                        <input type="checkbox" 
                               name="stock_bajo" 
                               value="1"
                               {{ request('stock_bajo') ? 'checked' : '' }}>
                        Solo cursos con stock bajo (< 10)
                    </label>
                </div>
                
                {{-- Botones --}}
                <div>
                    <button type="submit" style="padding: 8px 15px; background: #0066cc; color: white; border: none; cursor: pointer;">
                        🔍 Buscar
                    </button>
                    <a href="{{ route('cursos.index') }}" style="padding: 8px 15px; background: #666; color: white; text-decoration: none; display: inline-block;">
                        🔄 Limpiar filtros
                    </a>
                </div>
            </div>
        </form>
    </div>
    <a href="{{ route('cursos.export.pdf') }}" 
   style="background: #2E75B6; color: white; padding: 10px;">
    📄 Descargar PDF
    </a>
    <a href="{{ route('cursos.export.excel') }}"
        class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg">
        📄 Exportar a Excel
    </a>

    @if($cursos->isEmpty())
    <x-alerta tipo="alerta">
        {{ __('messages.no_courses') }}
    </x-alerta>
    
    @else

    <a href="{{ route('carrito.ver') }}" 
        class="inline-block mb-4 bg-blue-500 text-white px-4 py-2 rounded">
        🛒 Ver Carrito
    </a>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @foreach ($cursos as $curso)
        <x-tarjeta-curso :curso="$curso" :mostrar-vacantes="true" />
    @endforeach
</div>



    
    {{-- Información de resultados --}}
    <div style="margin: 20px 0; padding: 10px; background: #e8f4f8; border-left: 4px solid #0066cc;">
        <p>
            📊 Mostrando {{ $cursos->firstItem() ?? 0 }} - {{ $cursos->lastItem() ?? 0 }} 
            de {{ $cursos->total() }} cursos
        </p>
    </div>

    @endif
    
    {{-- Enlaces de paginación --}}
    <div style="margin: 20px 0;">
        {{ $cursos->appends(request()->query())->links() }}
    </div>



    </x-app-layout>
