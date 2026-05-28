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
        $extras = [
            'api_software_version' => config('site.software_version'),
            'ambiente' => config('site.ambiente'),
            'controller' => explode('\\', __CLASS__)[sizeof(explode('\\', __CLASS__))-1],
            'function' => __FUNCTION__,
            'method' => request()->method(),
            'url' => 'permissions',
        ];
        $data = Permission::all();
        if(empty($data)) {
            $response = [
                'estado' => 'empty',
                'message' => 'No se encontraron permisos',
                'code' => 0,
                'errors' => [],
                'data' => null,
                'extras' => $extras
            ];
            return response()->json($response, 200);
        }
        $response = [
            'estado' => 'ok',
            'message' => 'Permisos obtenidos con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $data,
            'extras' => $extras
        ];
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $extras = [
            'api_software_version' => config('site.software_version'),
            'ambiente' => config('site.ambiente'),
            'controller' => explode('\\', __CLASS__)[sizeof(explode('\\', __CLASS__))-1],
            'function' => __FUNCTION__,
            'method' => request()->method(),
            'url' => 'permissions',
        ];
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
                'extras' => $extras
            ];
            return response()->json($response, 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions',
        ]);
        $permission = Permission::create($validated);
        $role = Role::where('name', 'super administrador')->first();
        if($role) {
            $role->givePermissionTo($permission);
        }
        $response = [
            'estado' => 'ok',
            'message' => 'Permiso creado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $permission,
            'extras' => $extras
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $extras = [
            'api_software_version' => config('site.software_version'),
            'ambiente' => config('site.ambiente'),
            'controller' => explode('\\', __CLASS__)[sizeof(explode('\\', __CLASS__))-1],
            'function' => __FUNCTION__,
            'method' => request()->method(),
            'url' => 'permissions/' . $id,
        ];
        $permission = Permission::findOrFail($id);
        $response = [
            'estado' => 'ok',
            'message' => 'Permiso obtenido con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $permission,
            'extras' => $extras
        ];
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $extras = [
            'api_software_version' => config('site.software_version'),
            'ambiente' => config('site.ambiente'),
            'controller' => explode('\\', __CLASS__)[sizeof(explode('\\', __CLASS__))-1],
            'function' => __FUNCTION__,
            'method' => request()->method(),
            'url' => 'permissions/' . $id,
        ];
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
                'extras' => $extras
            ];
            return response()->json($response, 403);
        }
        $permission = Permission::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:permissions,name,' . $id,
        ]);
        $permission->update($validated);
        $response = [
            'estado' => 'ok',
            'message' => 'Permiso actualizado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $permission,
            'extras' => $extras
        ];
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $extras = [
            'api_software_version' => config('site.software_version'),
            'ambiente' => config('site.ambiente'),
            'controller' => explode('\\', __CLASS__)[sizeof(explode('\\', __CLASS__))-1],
            'function' => __FUNCTION__,
            'method' => request()->method(),
            'url' => 'permissions/' . $id,
        ];
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            $response = [
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
                'extras' => $extras
            ];
            return response()->json($response, 403);
        }
        $permission = Permission::findOrFail($id);
        $permission->delete();
        $response = [
            'estado' => 'ok',
            'message' => 'Permiso eliminado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => [],
            'extras' => $extras
        ];
        return response()->json($response, 200);
    }
}
