<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        // Siempre devolver JSON en caso de no autenticado
        abort(response()->json([
            'estado' => 'fail',
            'code' => -1,
            'errors' => ['No autenticado'],
            'message' => 'Debes iniciar sesión para acceder a este recurso.'
        ], 401));
    }
}
