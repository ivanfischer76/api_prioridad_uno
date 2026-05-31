<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'sender_user_id',
        'sender_type',
        'from_email',
        'body',
        'sent_via_email',
        'email_error',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_via_email' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function thread()
    {
        return $this->belongsTo(SupportThread::class, 'thread_id');
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
