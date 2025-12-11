<?php
namespace App\Notifications;
use App\Models\Curso;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NuevoCursoNotificacion extends Notification
{
    public $curso;
    public function __construct(Curso $curso)
    { $this->curso = $curso; }
    
    public function via($notifiable): array { return ['mail']; }
    
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo Curso')
            ->line('Nuevo: '.$this->curso->nombre)
            ->action('Ver', url('/cursos/'.$this->curso->id));
    }
}
