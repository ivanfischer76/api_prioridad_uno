<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Permission::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions',
        ]);
        $permission = Permission::create($validated);
        $role = Role::where('name', 'super administrador')->first();
        if($role) {
            $role->givePermissionTo($permission);
        }
        return response()->json($permission, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $permission = Permission::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:permissions,name,' . $id,
        ]);
        $permission->update($validated);
        return response()->json($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return response()->json(['message' => 'Permiso eliminado']);
    }
}
