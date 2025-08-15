<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campania;
use Illuminate\Http\Request;

class CampaniaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $campanias = Campania::all();
    return response()->json($campanias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user() || !auth()->user()->can('crear campañas')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'objetivo' => 'nullable|string',
            'resultado' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'proyecto_id' => 'nullable|exists:proyectos,id',
        ]);
        $campania = Campania::create($validated);
        return response()->json($campania->load('proyecto'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Campania $campania)
    {
    return response()->json($campania->load('proyecto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campania $campania)
    {
        if (!auth()->user() || !auth()->user()->can('actualizar campañas')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'objetivo' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'proyecto_id' => 'nullable|exists:proyectos,id',
        ]);
        $campania->update($validated);
        return response()->json($campania->load('proyecto'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campania $campania)
    {
        if (!auth()->user() || !auth()->user()->can('eliminar campañas')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $campania->delete();
        return response()->json(['message' => 'Campaña eliminada correctamente']);
    }
}
