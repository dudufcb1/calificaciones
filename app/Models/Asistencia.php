<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $fillable = [
        'alumno_id',
        'user_id',
        'fecha',
        'estado',
        'observaciones',
        'asistio',
        'justificacion'
    ];

    protected $casts = [
        'fecha' => 'date',
        'asistio' => 'boolean',
        'estado' => 'string',
    ];

    protected $appends = ['estado_normalizado'];

    /**
     * Accessor para asegurar que siempre tengamos un estado normalizado
     */
    public function getEstadoNormalizadoAttribute()
    {
        // Si por alguna razón estado es null o vacío, determinarlo basado en asistio y justificacion
        if (empty($this->estado)) {
            if ($this->asistio) {
                return 'asistio';
            } else {
                return !empty($this->justificacion) ? 'justificada' : 'falta';
            }
        }

        return $this->estado;
    }

    /**
     * Mutator para asegurar que el estado se guarda correctamente
     */
    public function setEstadoAttribute($value)
    {
        $this->attributes['estado'] = in_array($value, ['asistio', 'falta', 'justificada']) ? $value : 'asistio';
    }

    /**
     * Obtiene el alumno asociado a esta asistencia.
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * Obtiene el usuario (docente) que registró esta asistencia.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alcance para filtrar por un rango de fechas.
     */
    public function scopeFechaEntre($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Alcance para filtrar por alumno.
     */
    public function scopeDeAlumno($query, $alumnoId)
    {
        return $query->where('alumno_id', $alumnoId);
    }

    /**
     * Alcance para filtrar por estado.
     */
    public function scopeConEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
