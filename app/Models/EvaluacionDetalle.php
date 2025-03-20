<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EvaluacionDetalle extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_detalles';

    protected $fillable = [
        'evaluacion_id',
        'alumno_id',
        'promedio_final',
        'observaciones',
    ];

    protected $casts = [
        'promedio_final' => 'decimal:2',
    ];

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function criterios(): BelongsToMany
    {
        return $this->belongsToMany(Criterio::class, 'evaluacion_detalle_criterio')
                    ->withPivot(['calificacion', 'calificacion_ponderada'])
                    ->withTimestamps();
    }
}
