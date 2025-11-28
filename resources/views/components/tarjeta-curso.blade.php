<div style="border: 1px solid #ddd; padding: 15px; margin: 10px; border-radius: 8px;">
    @if($curso->foto)
        <img src="{{ asset('storage/' . $curso->foto) }}" 
             alt="{{ $curso->nombre }}" 
             style="max-width: 100px; height: auto;">
    @endif
    <h3>{{ $curso->nombre }}</h3>
    <p><strong>{{__('messages.price') }}</strong> {{ number_format($curso->precio, 2) }} €</p>
    @if($mostrarVacantes)
        <p><strong>{{ __('messages.vacantes') }}:</strong> {{ $curso->vacantes }}</p>
        <p><strong>{{ __('messages.start_date') }}:</strong> {{ $curso->fecha_inicio }} </p>
        <p><strong>{{ __('messages.end_date') }}:</strong> {{ $curso->fecha_fin }} </p>
    @endif
    <a href="{{ route('cursos.show', $curso->id) }}">{{ __('messages.see_details') }}</a>
</div>
