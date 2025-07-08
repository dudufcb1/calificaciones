<?php

namespace App\Http\Controllers;

use App\Models\Criterio;
use App\Models\CampoFormativo;
use Illuminate\Http\Request;

class CriterioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $criterios = Criterio::with('campoFormativo')->get();
        return response()->json($criterios);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|min:0|max:100',
            'descripcion' => 'nullable|string',
            'campo_formativo_id' => 'required|exists:campo_formativos,id'
        ]);

        // Verificar que la suma de porcentajes no exceda 100%
        $campoFormativo = CampoFormativo::findOrFail($request->campo_formativo_id);
        $sumaPorcentajes = $campoFormativo->criterios()->sum('porcentaje');

        if ($sumaPorcentajes + $request->porcentaje > 100) {
            return response()->json([
                'message' => 'La suma de porcentajes no puede exceder el 100%'
            ], 422);
        }

        // Verificar si hay evaluaciones finalizadas para este campo formativo
        $evaluacionesFinalizadas = \App\Models\Evaluacion::where('campo_formativo_id', $request->campo_formativo_id)
            ->where('is_draft', false)
            ->count();

        if ($evaluacionesFinalizadas > 0) {
            return response()->json([
                'message' => 'No se pueden agregar criterios a un campo formativo que tiene evaluaciones finalizadas. Esto podría causar inconsistencias en los datos.'
            ], 422);
        }

        $criterio = Criterio::create($request->all());
        return response()->json($criterio, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Criterio $criterio)
    {
        return response()->json($criterio->load('campoFormativo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Criterio $criterio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Criterio $criterio)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|min:0|max:100',
            'descripcion' => 'nullable|string',
            'campo_formativo_id' => 'required|exists:campo_formativos,id'
        ]);

        // Verificar si hay evaluaciones finalizadas para este campo formativo
        $evaluacionesFinalizadas = \App\Models\Evaluacion::where('campo_formativo_id', $criterio->campo_formativo_id)
            ->where('is_draft', false)
            ->count();

        if ($evaluacionesFinalizadas > 0) {
            return response()->json([
                'message' => 'No se pueden modificar criterios de un campo formativo que tiene evaluaciones finalizadas.'
            ], 422);
        }

        // Verificar que la suma de porcentajes no exceda 100%
        $campoFormativo = CampoFormativo::findOrFail($request->campo_formativo_id);
        $sumaPorcentajes = $campoFormativo->criterios()
            ->where('id', '!=', $criterio->id)
            ->sum('porcentaje');

        if ($sumaPorcentajes + $request->porcentaje > 100) {
            return response()->json([
                'message' => 'La suma de porcentajes no puede exceder el 100%'
            ], 422);
        }

        $criterio->update($request->all());
        return response()->json($criterio);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Criterio $criterio)
    {
        // Verificar si hay evaluaciones finalizadas para este campo formativo
        $evaluacionesFinalizadas = \App\Models\Evaluacion::where('campo_formativo_id', $criterio->campo_formativo_id)
            ->where('is_draft', false)
            ->count();

        if ($evaluacionesFinalizadas > 0) {
            return response()->json([
                'message' => 'No se pueden eliminar criterios de un campo formativo que tiene evaluaciones finalizadas.'
            ], 422);
        }

        $criterio->delete();
        return response()->json(null, 204);
    }
}
