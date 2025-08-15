<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['login'])
            ->orWhere('username', $credentials['login'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function register(Request $request)
    {
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
        // Asignar rol usuario
        $user->assignRole('usuario');
        $token = $user->createToken('api_token')->plainTextToken;
        // Enviar email de verificación estándar de Laravel
        $user->sendEmailVerificationNotification();
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Usuario registrado. Se ha enviado un email para confirmar la dirección.'
        ], 201);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Enlace de recuperación enviado'])
            : response()->json(['message' => 'No se pudo enviar el enlace'], 400);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Hash inválido'], 400);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El email ya está confirmado']);
        }
        $user->markEmailAsVerified();
        return response()->json(['message' => 'Email confirmado correctamente']);
    }
}