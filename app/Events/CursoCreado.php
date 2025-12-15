<?php
namespace App\Events;

use App\Models\Curso;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CursoCreado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $curso;
    
    public function __construct(Curso $curso)
    {
        $this->curso = $curso;
    }
}
