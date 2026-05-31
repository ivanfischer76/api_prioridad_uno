<?php

namespace App\Mail;

use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportMessageToSupportMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportThread $thread,
        public SupportMessage $message,
        public User $user,
    ) {}

    public function build()
    {
        return $this
            ->subject('Nuevo mensaje de contacto: '.$this->thread->subject)
            ->view('emails.support_message_to_support');
    }
}
