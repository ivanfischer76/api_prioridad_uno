<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovedadMotivoOracion extends Model
{
    protected $table = 'novedad_motivos_oracion';

    protected $fillable = [
        'novedad_id',
        'motivo',
        'orden',
    ];

    public function novedad()
    {
        return $this->belongsTo(Novedad::class, 'novedad_id');
    }
}
