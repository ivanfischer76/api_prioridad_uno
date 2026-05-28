<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Role::with(['permissions'])->get();
        $response = [
            'estado' => 'ok',
            'message' => 'Roles obtenidos con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $data,
        ];
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
            ];
            return response()->json($response, 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|unique:roles',
        ]);
        $role = Role::create($validated);
        $response = [
            'estado' => 'ok',
            'message' => 'Rol creado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $role,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
        $response = [
            'estado' => 'ok',
            'message' => 'Rol obtenido con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $role,
        ];
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
            ];
            return response()->json($response, 403);
        }
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $id,
        ]);
        $role->update($validated);
        $response = [
            'estado' => 'ok',
            'message' => 'Rol actualizado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $role,
        ];
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
            ];
            return response()->json($response, 403);
        }
        $role = Role::findOrFail($id);
        $role->delete();
        $response = [
            'estado' => 'ok',
            'message' => 'Rol eliminado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => [],
        ];
        return response()->json($response, 200);
    }
}
