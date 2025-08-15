<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campania extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'objetivo',
        'resultado',
        'fecha_inicio',
        'fecha_fin',
        'proyecto_id',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }
}
