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
        'orden',
        'user_id',
        'es_asistencia',
    ];

    protected $casts = [
        'es_asistencia' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope('user', function ($query) {
            if (auth()->check()) {
                $query->where('criterios.user_id', auth()->id());
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && is_null($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campoFormativo()
    {
        return $this->belongsTo(CampoFormativo::class);
    }
}
