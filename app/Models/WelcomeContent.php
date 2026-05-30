<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_path',
        'disk',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function translations()
    {
        return $this->hasMany(WelcomeContentTranslation::class);
    }
}
