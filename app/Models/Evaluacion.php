<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evaluacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluaciones';

    protected $fillable = [
        'campo_formativo_id',
        'alumno_id',
        'promedio_final',
        'is_draft',
    ];

    protected $casts = [
        'promedio_final' => 'decimal:2',
        'is_draft' => 'boolean',
    ];

    public function campoFormativo(): BelongsTo
    {
        return $this->belongsTo(CampoFormativo::class);
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function criterios(): BelongsToMany
    {
        return $this->belongsToMany(Criterio::class, 'evaluacion_criterio')
                    ->withPivot(['calificacion', 'calificacion_ponderada'])
                    ->withTimestamps();
    }

    public function recalcularPromedio(): void
    {
        $this->promedio_final = $this->criterios()
            ->get()
            ->sum('pivot.calificacion_ponderada');

        $this->save();
    }
}
