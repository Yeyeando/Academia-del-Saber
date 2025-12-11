<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WelcomeMail extends Mailable
{
    public $userName;
    
    public function __construct($userName)
    { $this->userName = $userName; }
    
    public function envelope(): Envelope
    { return new Envelope(subject: 'Bienvenido a Academia del Saber'); }
    
    public function content(): Content
    { return new Content(markdown: 'emails.welcome'); }
    
    public function attachments(): array { return []; }
}
