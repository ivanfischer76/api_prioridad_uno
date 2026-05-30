<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    protected $table = 'novedades';

    protected $fillable = [
        'titulo',
        'markdown',
        'fecha',
        'proyecto_id',
        'titulo_plano',
        'markdown_plano',
    ];

    protected $casts = [
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function archivos()
    {
        return $this->hasMany(NovedadArchivo::class, 'novedad_id');
    }

    public function motivosOracion()
    {
        return $this->hasMany(NovedadMotivoOracion::class, 'novedad_id');
    }
}
