<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovedadArchivo extends Model
{
    protected $fillable = [
        'novedad_id',
        'archivo',
        'tipo',
    ];

    public function novedad()
    {
        return $this->belongsTo(Novedad::class, 'novedad_id');
    }
}
