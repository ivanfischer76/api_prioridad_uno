<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_date',
        'fingerprint',
        'path',
        'hits',
        'first_visited_at',
        'last_visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'first_visited_at' => 'datetime',
            'last_visited_at' => 'datetime',
        ];
    }
}
