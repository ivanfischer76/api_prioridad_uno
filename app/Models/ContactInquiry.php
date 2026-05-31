<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_user_id',
        'full_name',
        'email',
        'subject',
        'message',
        'leido',
        'contestado',
        'fecha_contacto',
        'fecha_respuesta',
        'status',
        'read_at',
        'admin_reply',
        'replied_by_user_id',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'leido' => 'boolean',
            'contestado' => 'boolean',
            'fecha_contacto' => 'datetime',
            'fecha_respuesta' => 'datetime',
            'read_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function repliedByUser()
    {
        return $this->belongsTo(User::class, 'replied_by_user_id');
    }

    public function senderUser()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
