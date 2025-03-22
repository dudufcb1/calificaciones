<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Momento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fecha',
        'fecha_inicio',
        'fecha_fin',
        'ciclo_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    /**
     * Obtener el ciclo al que pertenece este momento
     */
    public function ciclo(): BelongsTo
    {
        return $this->belongsTo(Ciclo::class);
    }

    /**
     * Obtener los campos formativos asociados a este momento
     */
    public function camposFormativos(): BelongsToMany
    {
        return $this->belongsToMany(CampoFormativo::class, 'momento_campo_formativo');
    }

    /**
     * Determinar si el momento tiene rango de fechas definido
     */
    public function tieneRangoFechas(): bool
    {
        return !is_null($this->fecha_inicio) && !is_null($this->fecha_fin);
    }
}
