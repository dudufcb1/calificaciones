<?php

namespace App\Enums;

enum MomentoEvaluacion: string
{
    case PRIMER_MOMENTO = 'PRIMER MOMENTO';
    case SEGUNDO_MOMENTO = 'SEGUNDO MOMENTO';
    case TERCER_MOMENTO = 'TERCER MOMENTO';
    case CUARTO_MOMENTO = 'CUARTO MOMENTO';

    public static function toArray(): array
    {
        return [
            self::PRIMER_MOMENTO->value => 'Primer Momento',
            self::SEGUNDO_MOMENTO->value => 'Segundo Momento',
            self::TERCER_MOMENTO->value => 'Tercer Momento',
            self::CUARTO_MOMENTO->value => 'Cuarto Momento',
        ];
    }
}
