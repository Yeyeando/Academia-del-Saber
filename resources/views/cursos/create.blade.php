<x-app-layout>

<h1>{{ __('messages.new_course') }}</h1>

@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li class="text-red-600">{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="/cursos" enctype="multipart/form-data">
    @csrf

    <label for="nombre">{{ __('messages.name') }}</label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}">
    <br>

    <label for="precio">{{ __('messages.price') }}
        <input type="number" step="0.01" name="precio" id="precio" value="{{ old('precio') }}">
    </label><br>

    <label for="vacantes">{{ __('messages.vacantes') }}
        <input type="number" name="vacantes" id="vacantes" value="{{ old('vacantes') }}">
    </label><br>

    <label for="fecha_inicio">{{ __('messages.start_date') }}
        <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}">
    </label><br>

    <label for="fecha_fin">{{ __('messages.end_date') }}
        <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}">
    </label><br><br>

    <label>{{ __('messages.photo') }}
        <input type="file" name="foto" accept="image/*">
    </label>

    @error('foto')
        <small style="color:red">{{ $message }}</small>
    @enderror

    <br>
    <label for="categoria_id">Categoría:</label>
    <select name="categoria_id" id="categoria_id">
        <option value="">Sin categoría</option>
        @foreach(App\Models\Categoria::all() as $cat)
            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
        @endforeach
    </select>

    <br>

    <a href="{{ route('cursos.index') }}">{{ __('messages.back_to_list') }}</a>
    <button type="submit">{{ __('messages.save') }}</button>
</form>
</x-app-layout>