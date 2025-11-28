<?php
namespace App\View\Components;
use Illuminate\View\Component;

class TarjetaCurso extends Component {
    public $curso;
    public $mostrarVacantes;

    public function __construct($curso, $mostrarVacantes = true)
    {
        $this->curso = $curso;
        $this->mostrarVacantes = $mostrarVacantes;
    }

    public function render()
    {
        return view('components.tarjeta-curso');
    }
}
