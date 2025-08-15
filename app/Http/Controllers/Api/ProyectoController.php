<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Proyecto::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user() || !auth()->user()->can('crear proyectos')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'nombre' => 'required|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);
        $proyecto = Proyecto::create($validated);
        return response()->json($proyecto, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Proyecto $proyecto)
    {
        return response()->json($proyecto);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Proyecto $proyecto)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        // Permitir solo a super admin, admin o misioneros asignados
        if (
            !$user->hasRole('super administrador') &&
            !$user->hasRole('administrador') &&
            !($user->hasRole('misionero') && $proyecto->misioneros->contains($user->id))
        ) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'nombre' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
        ]);
        $proyecto->update($validated);
        return response()->json($proyecto);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proyecto $proyecto)
    {
        if (!auth()->user() || !auth()->user()->can('eliminar proyectos')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $proyecto->delete();
        return response()->json(['message' => 'Proyecto eliminado']);
    }

    /**
     * Asignar misioneros al proyecto.
     */
    public function asignarMisioneros(Request $request, Proyecto $proyecto)
    {
        $request->validate([
            'misioneros' => 'required|array',
            'misioneros.*' => 'exists:users,id',
        ]);
        $misionerosValidos = User::whereIn('id', $request->misioneros)
            ->whereHas('roles', function($q) {
                $q->where('name', 'misionero');
            })
            ->pluck('id')
            ->toArray();
        if (empty($misionerosValidos)) {
            return response()->json([
                'message' => 'Ningún usuario con rol misionero fue asignado.'
            ], 422);
        }
        $proyecto->misioneros()->sync($misionerosValidos);
        return response()->json([
            'message' => 'Misioneros asignados',
            'misioneros' => $proyecto->misioneros
        ]);
    }

    /**
     * Ver los misioneros asignados a un proyecto.
     */
    public function verMisioneros(Proyecto $proyecto)
    {
        return response()->json($proyecto->misioneros);
    }
}
