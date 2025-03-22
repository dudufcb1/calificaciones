<?php

namespace App\Models;

use App\Enums\MomentoEvaluacion;
use App\Models\User;
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
        'titulo',
        'descripcion',
        'fecha_evaluacion',
        'campo_formativo_id',
        'is_draft',
        'user_id',
        'momento',
    ];

    protected $casts = [
        'fecha_evaluacion' => 'date',
        'is_draft' => 'boolean',
        'momento' => MomentoEvaluacion::class,
    ];

    protected static function booted()
    {
        static::addGlobalScope('user', function ($query) {
            if (auth()->check()) {
                $query->where('evaluaciones.user_id', auth()->id());
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && is_null($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
