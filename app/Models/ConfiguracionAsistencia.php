<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionAsistencia extends Model
{
    protected $fillable = [
        'user_id',
        'mes',
        'anio',
        'dias_habiles',
        'es_periodo_vacacional'
    ];

    protected $casts = [
        'es_periodo_vacacional' => 'boolean',
    ];

    /**
     * Obtiene el usuario (docente) que creó esta configuración.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alcance para filtrar por un mes específico.
     */
    public function scopeDelMes($query, $mes)
    {
        return $query->where('mes', $mes);
    }

    /**
     * Alcance para filtrar por un año específico.
     */
    public function scopeDelAnio($query, $anio)
    {
        return $query->where('anio', $anio);
    }

    /**
     * Obtiene el nombre del mes en español.
     */
    public function getNombreMesAttribute()
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        return $meses[$this->mes] ?? 'Desconocido';
    }
}
