<?php
use Illuminate\Support\Facades\Route;

// Rutas públicas de la API
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaniaController;
use App\Http\Controllers\Api\NovedadArchivoController;
use App\Http\Controllers\Api\NovedadController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VersionController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// Rutas públicas para ver proyectos y campañas
Route::get('proyectos', [ProyectoController::class, 'index']);
Route::get('proyectos/{proyecto}', [ProyectoController::class, 'show']);
Route::get('proyectos/{proyecto}/misioneros', [ProyectoController::class, 'verMisioneros']);
Route::get('campanias', [CampaniaController::class, 'index']);
Route::get('campanias/{campania}', [CampaniaController::class, 'show']);

// Ruta para ver la versión de la api
Route::get('/version', [VersionController::class, 'show']);

// Rutas protegidas para crear, actualizar y eliminar proyectos y campañas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('proyectos', [ProyectoController::class, 'store']);
    Route::put('proyectos/{proyecto}', [ProyectoController::class, 'update']);
    Route::patch('proyectos/{proyecto}', [ProyectoController::class, 'update']);
    Route::delete('proyectos/{proyecto}', [ProyectoController::class, 'destroy']);
    Route::post('proyectos/{proyecto}/misioneros', [ProyectoController::class, 'asignarMisioneros']);
    Route::post('campanias', [CampaniaController::class, 'store']);
    Route::put('campanias/{campania}', [CampaniaController::class, 'update']);
    Route::patch('campanias/{campania}', [CampaniaController::class, 'update']);
    Route::delete('campanias/{campania}', [CampaniaController::class, 'destroy']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
});

// Rutas protegidas para asociar y quitar archivos de novedades
Route::middleware('auth:sanctum')->group(function () {
    Route::post('novedad-archivos', [NovedadArchivoController::class, 'store']);
    Route::delete('novedad-archivos/{novedadArchivo}', [NovedadArchivoController::class, 'destroy']);
});
// Rutas protegidas para novedades
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('novedades', NovedadController::class);
});
// Rutas para subir y eliminar archivos
Route::post('uploads', [UploadController::class, 'upload'])->middleware('auth:sanctum');
Route::delete('uploads', [UploadController::class, 'delete'])->middleware('auth:sanctum');
