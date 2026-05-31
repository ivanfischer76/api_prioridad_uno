<?php

namespace App\Mail;

use App\Models\SupportMessage;
use App\Models\SupportThread;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportReplyToUserMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportThread $thread,
        public SupportMessage $message,
    ) {}

    public function build()
    {
        return $this
            ->subject('Respuesta a tu consulta: '.$this->thread->subject)
            ->view('emails.support_reply_to_user');
    }
}
