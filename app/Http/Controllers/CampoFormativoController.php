<?php

namespace App\Http\Controllers;

use App\Models\CampoFormativo;
use Illuminate\Http\Request;

class CampoFormativoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $camposFormativos = CampoFormativo::with('criterios')->get();
        return response()->json($camposFormativos);
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
            'descripcion' => 'nullable|string',
        ]);

        $campoFormativo = CampoFormativo::create($request->all());
        return response()->json($campoFormativo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CampoFormativo $campoFormativo)
    {
        return response()->json($campoFormativo->load('criterios'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CampoFormativo $campoFormativo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $campoFormativo->update($request->all());
        return response()->json($campoFormativo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CampoFormativo $campoFormativo)
    {
        $campoFormativo->delete();
        return response()->json(null, 204);
    }
}
