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
    
    @if($cursos->isEmpty())
    <x-alerta tipo="alerta">
        {{ __('messages.no_courses') }}
    </x-alerta>
    @else

    @foreach ($cursos as $curso)
        <li>
            <x-tarjeta-curso :curso="$curso" :mostrar-vacantes="true" />
        </li>
    @endforeach

    @endif
    </x-app-layout>
