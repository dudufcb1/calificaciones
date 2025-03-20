<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluaciones';

    protected $fillable = [
        'campo_formativo_id',
        'titulo',
        'descripcion',
        'fecha_evaluacion',
        'is_draft',
    ];

    protected $casts = [
        'fecha_evaluacion' => 'date',
        'is_draft' => 'boolean',
    ];

    public function campoFormativo(): BelongsTo
    {
        return $this->belongsTo(CampoFormativo::class);
    }

    public function criterios(): BelongsToMany
    {
        return $this->belongsToMany(Criterio::class, 'evaluacion_criterio')
                    ->withPivot(['calificacion', 'calificacion_ponderada'])
                    ->withTimestamps();
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(EvaluacionDetalle::class);
    }

    public function recalcularPromedio(): void
    {
        $this->promedio_final = $this->criterios()
            ->get()
            ->sum('pivot.calificacion_ponderada');

        $this->save();
    }
}
