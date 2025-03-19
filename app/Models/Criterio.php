<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Criterio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'porcentaje',
        'descripcion',
        'campo_formativo_id',
    ];

    public function campoFormativo()
    {
        return $this->belongsTo(CampoFormativo::class);
    }
}
