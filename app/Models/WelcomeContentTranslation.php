<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeContentTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'welcome_content_id',
        'locale',
        'verse_text',
        'verse_citation',
        'reflection_text',
    ];

    public function welcomeContent()
    {
        return $this->belongsTo(WelcomeContent::class);
    }
}
