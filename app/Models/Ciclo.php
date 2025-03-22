<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ciclo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'anio_inicio',
        'anio_fin',
        'activo',
        'user_id'
    ];

    /**
     * Obtener los momentos asociados al ciclo
     */
    public function momentos(): HasMany
    {
        return $this->hasMany(Momento::class);
    }

    /**
     * Obtener el usuario al que pertenece este ciclo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener nombre formateado del ciclo
     */
    public function getNombreFormateadoAttribute(): string
    {
        return "{$this->nombre} ({$this->anio_inicio}-{$this->anio_fin})";
    }
}
