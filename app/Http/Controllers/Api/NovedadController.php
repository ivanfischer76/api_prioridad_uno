<?php

namespace App\Http\Controllers\Api;

use App\Models\Novedad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NovedadController extends Controller
{
    public function index()
    {
    return response()->json(Novedad::with('archivos')->get());
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'motivos_oracion' => 'nullable|string',
            'fecha' => 'nullable|date',
            'proyecto_id' => 'required|exists:proyectos,id',
        ]);
        $proyecto = \App\Models\Proyecto::find($validated['proyecto_id']);
        if (
            !$user->hasRole('super administrador') &&
            !$user->hasRole('administrador') &&
            !($user->hasRole('misionero') && $proyecto && $proyecto->misioneros->contains($user->id))
        ) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $novedad = Novedad::create($validated);
        return response()->json($novedad, 201);
    }

    public function show(Novedad $novedad)
    {
        return response()->json($novedad);
    }

    public function update(Request $request, Novedad $novedad)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $proyecto = $novedad->proyecto;
        if (
            !$user->hasRole('super administrador') &&
            !$user->hasRole('administrador') &&
            !($user->hasRole('misionero') && $proyecto && $proyecto->misioneros->contains($user->id))
        ) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'nullable|date',
            'proyecto_id' => 'sometimes|required|exists:proyectos,id',
        ]);
        $novedad->update($validated);
        return response()->json($novedad);
    }

    public function destroy(Novedad $novedad)
    {
        $novedad->delete();
        return response()->json(['message' => 'Novedad eliminada correctamente']);
    }
}
