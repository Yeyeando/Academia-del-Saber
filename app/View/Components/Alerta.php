<?php
namespace App\View\Components;
use Illuminate\View\Component;

class Alerta extends Component {
    public $tipo;

    public function __construct($tipo = 'info')
    {
        $this->tipo = $tipo;
    }

    public function colorDeFondo()
    {
        return match($this->tipo) {
            'exito' => '#d4edda',
            'error' => '#f8d7da',
            'alerta' => '#fff3cd',
            default => '#d1ecf1'
        };
    }

    public function render()
    {
        return view('components.alerta');
    }
}
