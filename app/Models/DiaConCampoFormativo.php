<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaConCampoFormativo extends Model
{
    use HasFactory;

    protected $table = 'dias_con_campos_formativos';

    protected $fillable = [
        'fecha',
        'grupo_id',
        'campo_formativo_id'
    ];

    protected $casts = [
        'fecha' => 'date'
    ];

    /**
     * Obtener el grupo asociado a este registro
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * Obtener el campo formativo asociado a este registro
     */
    public function campoFormativo(): BelongsTo
    {
        return $this->belongsTo(CampoFormativo::class);
    }

    /**
     * Obtener los días con campos formativos de un grupo específico en un rango de fechas
     */
    public static function obtenerPorGrupoYMes($grupo_id, $anio, $mes)
    {
        $fechaInicio = \Carbon\Carbon::createFromDate($anio, $mes, 1)->format('Y-m-d');
        $fechaFin = \Carbon\Carbon::createFromDate($anio, $mes, 1)->endOfMonth()->format('Y-m-d');

        return self::where('grupo_id', $grupo_id)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with('campoFormativo')
            ->get();
    }
}
