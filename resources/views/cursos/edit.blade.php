<x-app-layout>

<h1>{{ __('messages.edit_course') }}</h1>

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('cursos.update', $curso->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <label for="nombre">{{ __('messages.name') }}</label>
    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $curso->nombre) }}">
    <br>

    <label for="precio">{{ __('messages.price') }}</label>
    <input type="number" step="0.01" name="precio" id="precio" value="{{ old('precio', $curso->precio) }}">
    <br>

    <label for="vacantes">{{ __('messages.vacantes') }}</label>
    <input type="number" name="vacantes" id="vacantes" value="{{ old('vacantes', $curso->vacantes) }}">
    <br>

    <label for="fecha_inicio">{{ __('messages.start_date') }}</label>
    <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio', $curso->fecha_inicio) }}">
    <br>

    <label for="fecha_fin">{{ __('messages.end_date') }}</label>
    <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin', $curso->fecha_fin) }}">
    <br><br>

    <label>{{ __('messages.photo') }}</label>
    <input type="file" name="foto" accept="image/*">
    <br>

    @if($curso->foto)
        <p>Foto actual:</p>
        <img src="{{ asset('storage/' . $curso->foto) }}" alt="Foto del curso" style="max-width: 150px;">
    @endif

    <br><br>

    <label for="categoria_id">Categoría:</label>
    <select name="categoria_id" id="categoria_id">
        @foreach(App\Models\Categoria::all() as $cat)
            <option value="{{ $cat->id }}" 
                {{ old('categoria_id', $curso->categoria_id) == $cat->id ? 'selected' : '' }}>
                {{ $cat->nombre }}
            </option>
        @endforeach
    </select>

    <br><br>

    <a href="{{ route('cursos.index') }}">{{ __('messages.back_to_list') }}</a>
    <button type="submit">{{ __('messages.save') }}</button>
</form>

{{-- BOTÓN ELIMINAR (solo admin gracias a policy) --}}
@can('delete', $curso)
    <hr>
    <form action="{{ route('cursos.destroy', $curso->id) }}" method="POST">
        @csrf
        @method('DELETE')

        <button type="submit" 
            style="color: white; background: red; padding: 8px 15px; border: none; margin-top: 20px;"
            onclick="return confirm('¿Seguro que quieres eliminar este curso?')">
            🗑 Eliminar curso
        </button>
    </form>
@endcan

</x-app-layout>
