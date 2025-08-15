<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function misioneros()
    {
        return $this->belongsToMany(User::class, 'misionero_proyecto');
    }

    public function campanias()
    {
        return $this->hasMany(Campania::class, 'proyecto_id');
    }

    public function novedades()
    {
        return $this->hasMany(Novedad::class, 'proyecto_id');
    }
}
