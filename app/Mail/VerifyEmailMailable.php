<?php
namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyEmailMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $url;
    public $locale;

    public function __construct($user, $url, $locale = 'es')
    {
        $this->user = $user;
        $this->url = $url;
        $this->locale = $locale;
    }

    public function build()
    {
        $subject = [
            'es' => 'Confirmar Email',
            'en' => 'Confirm Email',
            'de' => 'E-Mail bestätigen',
            'fr' => "Confirmer l'email",
            'pt' => 'Confirmar Email',
            'it' => 'Conferma Email',
        ];
        $button = [
            'es' => 'Confirmar Email',
            'en' => 'Confirm Email',
            'de' => 'E-Mail bestätigen',
            'fr' => "Confirmer l'email",
            'pt' => 'Confirmar Email',
            'it' => 'Conferma Email',
        ];
        $messages = [
            'es' => 'Por favor confirma tu dirección de correo electrónico para completar el registro.',
            'en' => 'Please confirm your email address to complete registration.',
            'de' => 'Bitte bestätigen Sie Ihre E-Mail-Adresse, um die Registrierung abzuschließen.',
            'fr' => "Veuillez confirmer votre adresse e-mail pour terminer l'inscription.",
            'pt' => 'Por favor, confirme seu endereço de e-mail para concluir o registro.',
            'it' => 'Conferma il tuo indirizzo email per completare la registrazione.',
        ];
        $locale = $this->locale;
        $message_text = $messages[$locale] ?? $messages['es'];
        return $this->subject($subject[$locale] ?? $subject['es'])
            ->view('emails.verify_email')
            ->with([
                'user' => $this->user,
                'url' => $this->url,
                'button' => $button[$locale] ?? $button['es'],
                'message_text' => $message_text,
            ]);
    }
}
