<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::with('roles', 'permissions')->get();
        if(!$data) {
            $response = [
                'estado' => 'empty',
                'message' => 'No se encontraron usuarios',
                'code' => 0,
                'errors' => [],
                'data' => [],
            ];
            return response()->json($response, 200);
        }
        $response = [
            'estado' => 'ok',
            'message' => 'Usuarios obtenidos con éxito',
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
            'username' => 'required|string|unique:users',
            'apellido' => 'required|string',
            'nombre' => 'required|string',
            'iglesia' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        $response = [
            'estado' => 'ok',
            'message' => 'Usuario creado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $user,
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $response = [
            'estado' => 'ok',
            'message' => 'Usuario obtenido con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $user,
        ];
        return response()->json($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user() || !auth()->user()->hasRole('super administrador')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'username' => 'sometimes|string|unique:users,username,' . $id,
            'apellido' => 'sometimes|string',
            'nombre' => 'sometimes|string',
            'iglesia' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
        ]);
        if(isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);
        $response = [
            'estado' => 'ok',
            'message' => 'Usuario actualizado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $user,
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
        $user = User::findOrFail($id);
        $user->delete();
        $response = [
            'estado' => 'ok',
            'message' => 'Usuario eliminado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => [],
        ];
        return response()->json($response, 200);
    }
}
