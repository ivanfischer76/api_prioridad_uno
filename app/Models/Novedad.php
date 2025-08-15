<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novedad extends Model
{
    protected $table = 'novedades';
    protected $fillable = [
        'titulo',
        'descripcion',
        'motivos_oracion',
        'fecha',
        'proyecto_id',
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function archivos()
    {
        return $this->hasMany(NovedadArchivo::class, 'novedad_id');
    }
}
