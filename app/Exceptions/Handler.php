<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Si es una excepción de autenticación, devolver JSON
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'estado' => 'fail',
                'code' => -1,
                'errors' => ['No autenticado'],
                'message' => 'Debes iniciar sesión para acceder a este recurso.'
            ], 401);
        }
        return parent::render($request, $exception);
    }
}
