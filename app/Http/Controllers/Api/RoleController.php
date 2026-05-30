<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Role::with(['permissions'])
            ->where('guard_name', 'api')
            ->get();
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
            'name' => 'required|string|unique:roles,name,NULL,id,guard_name,api',
            'permissions' => 'sometimes|array',
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'api')),
            ],
        ]);
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'api',
        ]);

        if (array_key_exists('permissions', $validated)) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');
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
        $role = Role::with(['permissions'])
            ->where('guard_name', 'api')
            ->findOrFail($id);
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
        $role = Role::where('guard_name', 'api')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $id . ',id,guard_name,api',
            'permissions' => 'sometimes|array',
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'api')),
            ],
        ]);

        if ($role->name === 'super administrador' && array_key_exists('name', $validated)) {
            return response()->json([
                'estado' => 'error',
                'message' => 'No se puede editar el nombre del rol super administrador',
                'code' => 0,
                'errors' => ['No se puede editar el nombre del rol super administrador'],
                'data' => [],
            ], 422);
        }

        if (array_key_exists('name', $validated)) {
            $role->update([
                'name' => $validated['name'],
            ]);
        }

        if (array_key_exists('permissions', $validated)) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');
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
        $role = Role::where('guard_name', 'api')->findOrFail($id);

        if ($role->name === 'super administrador') {
            return response()->json([
                'estado' => 'error',
                'message' => 'No se puede eliminar el rol super administrador',
                'code' => 0,
                'errors' => ['No se puede eliminar el rol super administrador'],
                'data' => [],
            ], 422);
        }

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
