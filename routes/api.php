<?php

use App\Http\Controllers\CriterioController;
use App\Http\Controllers\API\AlumnoDataController;
use App\Http\Middleware\ValidateAIAgentToken;
use Illuminate\Support\Facades\Route;

Route::resource('criterios', CriterioController::class);

// Endpoint para el agente de IA (protegido con middleware de validación de token)
Route::post('/alumnos-data', [AlumnoDataController::class, 'getAlumnosData'])
    ->middleware(ValidateAIAgentToken::class);
