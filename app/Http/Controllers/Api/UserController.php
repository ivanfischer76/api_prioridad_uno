<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

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
            'fecha_nacimiento' => 'nullable|date|before:today',
            'iglesia' => 'required|string',
            'idioma' => 'sometimes|nullable|string|in:es,en,pt,it,fr,de',
            'notificarme' => 'sometimes|boolean',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $validated['password'] = Hash::make($validated['password']);

        try {
            $user = DB::transaction(function () use ($validated, $request) {
                $user = User::create($validated);

                $defaultRole = Role::where('name', 'usuario')
                    ->where('guard_name', 'api')
                    ->firstOrFail();
                $user->assignRole($defaultRole);

                if ($request->filled('role') && $request->input('role') !== 'usuario') {
                    $selectedRole = Role::where('name', $request->input('role'))
                        ->where('guard_name', 'api')
                        ->firstOrFail();
                    $user->syncRoles([$selectedRole]);
                }

                $user->load('roles', 'permissions');
                return $user;
            });
        } catch (\Throwable $e) {
            return response()->json([
                'estado' => 'error',
                'message' => 'No se pudo crear el usuario: ' . $e->getMessage(),
                'code' => 0,
                'errors' => [$e->getMessage()],
                'data' => [],
            ], 422);
        }

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
            'fecha_nacimiento' => 'sometimes|nullable|date|before:today',
            'iglesia' => 'sometimes|nullable|string',
            'idioma' => 'sometimes|nullable|string|in:es,en,pt,it,fr,de',
            'notificarme' => 'sometimes|boolean',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6',
            'permissions' => 'sometimes|array',
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'api')),
            ],
        ]);
        if(isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);

        if ($request->filled('role')) {
            $role = Role::where('name', $request->input('role'))
                ->where('guard_name', 'api')
                ->first();
            if ($role) {
                $user->syncRoles([$role]);
            }
        }

        if (array_key_exists('permissions', $validated)) {
            $user->syncPermissions($validated['permissions']);
        }

        $user->load('roles', 'permissions');

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
     * Actualiza el perfil del usuario autenticado.
     */
    public function updateProfile(Request $request)
    {
        $authUser = auth()->user();

        if (!$authUser) {
            return response()->json([
                'estado' => 'error',
                'message' => 'No autorizado',
                'code' => 0,
                'errors' => ['No autorizado'],
                'data' => [],
            ], 403);
        }

        $validated = $request->validate([
            'username' => 'required|string|unique:users,username,' . $authUser->id,
            'apellido' => 'required|string',
            'nombre' => 'required|string',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'iglesia' => 'nullable|string',
            'idioma' => 'sometimes|nullable|string|in:es,en,pt,it,fr,de',
            'notificarme' => 'sometimes|boolean',
            'email' => 'required|email|unique:users,email,' . $authUser->id,
        ]);

        $authUser->update($validated);
        $authUser->load('roles', 'permissions');

        $userData = $authUser->toArray();
        $userData['roles'] = $authUser->roles;
        $userData['permissions'] = $authUser->getAllPermissions();

        return response()->json([
            'estado' => 'ok',
            'message' => 'Perfil actualizado con éxito',
            'code' => 1,
            'errors' => [],
            'data' => $userData,
        ], 200);
    }

    /**
     * Cambia la contraseña del usuario autenticado.
     */
    public function changePassword(Request $request)
    {
        $authUser = auth()->user();

        if (!$authUser) {
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
            'password' => 'required|string|min:6|confirmed',
        ]);

        $authUser->password = Hash::make($validated['password']);
        $authUser->save();

        $response = [
            'estado' => 'ok',
            'message' => 'Contraseña actualizada con éxito',
            'code' => 1,
            'errors' => [],
            'data' => [],
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

        if ((int) $user->id === 1 && $user->hasRole('super administrador')) {
            $response = [
                'estado' => 'error',
                'message' => 'No se puede eliminar al super administrador principal',
                'code' => 0,
                'errors' => ['No se puede eliminar al super administrador principal'],
                'data' => [],
            ];

            return response()->json($response, 422);
        }

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
