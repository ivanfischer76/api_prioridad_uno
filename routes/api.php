<?php
use Illuminate\Support\Facades\Route;

// Rutas públicas de la API
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaniaController;
use App\Http\Controllers\Api\NovedadController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProyectoController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ArchivosController;
use App\Http\Controllers\Api\SiteVisitController;
use App\Http\Controllers\Api\ContactSupportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VersionController;
use App\Http\Controllers\Api\WelcomeContentController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// Rutas públicas para ver proyectos y campañas
Route::get('proyectos', [ProyectoController::class, 'index']);
Route::get('proyectos/{proyecto}', [ProyectoController::class, 'show']);
Route::get('proyectos/{proyecto}/misioneros', [ProyectoController::class, 'verMisioneros']);
Route::get('novedades', [NovedadController::class, 'index']);
Route::get('novedades/{novedad}', [NovedadController::class, 'show']);

// Ruta para ver la versión de la api
Route::get('/version', [VersionController::class, 'show']);
Route::post('/visits/track', [SiteVisitController::class, 'track']);
Route::get('/visits/stats', [SiteVisitController::class, 'stats']);
Route::get('/welcome-content', [WelcomeContentController::class, 'show']);
Route::get('/contact-channel', [ContactSupportController::class, 'getChannel']);
Route::post('/contact-us/messages', [ContactSupportController::class, 'submitPublicContact']);

// Rutas públicas
Route::get('/check-db', [App\Http\Controllers\Api\SiteController::class, 'checkDatabase']);

// Rutas protegidas para crear, actualizar y eliminar proyectos y campañas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('users/change-password', [UserController::class, 'changePassword']);
    Route::put('users/profile', [UserController::class, 'updateProfile']);
    // Route::post('proyectos', [ProyectoController::class, 'store']);
    // Route::put('proyectos/{proyecto}', [ProyectoController::class, 'update']);
    // Route::patch('proyectos/{proyecto}', [ProyectoController::class, 'update']);
    // Route::delete('proyectos/{proyecto}', [ProyectoController::class, 'destroy']);
    // Route::post('proyectos/{proyecto}/misioneros', [ProyectoController::class, 'asignarMisioneros']);
    // Route::post('campanias', [CampaniaController::class, 'store']);
    // Route::put('campanias/{campania}', [CampaniaController::class, 'update']);
    // Route::patch('campanias/{campania}', [CampaniaController::class, 'update']);
    // Route::delete('campanias/{campania}', [CampaniaController::class, 'destroy']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::get('admin/welcome-content', [WelcomeContentController::class, 'showAdmin']);
    Route::put('admin/welcome-content/translations/{locale}', [WelcomeContentController::class, 'updateTranslation']);
    Route::post('admin/welcome-content/image', [WelcomeContentController::class, 'uploadImage']);

    Route::post('support/messages', [ContactSupportController::class, 'sendMessage']);
    Route::get('support/threads', [ContactSupportController::class, 'myThreads']);
    Route::get('support/threads/{thread}', [ContactSupportController::class, 'myThreadMessages']);

    Route::middleware('permission:gestionar sistema')->group(function () {
        Route::post('admin/support/threads/{thread}/reply', [ContactSupportController::class, 'adminReply']);
    });

    Route::middleware('permission:gestionar contactos')->group(function () {
        Route::get('admin/contact-us/messages', [ContactSupportController::class, 'adminInquiries']);
    });

    Route::middleware('permission:gestionar contactos')->group(function () {
        Route::get('admin/contact-us/messages/{inquiry}', [ContactSupportController::class, 'adminInquiryDetail']);
        Route::post('admin/contact-us/messages/{inquiry}/reply', [ContactSupportController::class, 'adminReplyInquiry']);
    });
});

// Rutas protegidas para novedades
Route::middleware('auth:sanctum')->group(function () {
    Route::post('novedades', [NovedadController::class, 'store']);
    Route::put('novedades/{novedad}', [NovedadController::class, 'update']);
    Route::patch('novedades/{novedad}', [NovedadController::class, 'update']);
    Route::delete('novedades/{novedad}', [NovedadController::class, 'destroy']);
});
// Rutas protegidas para subir, ordenar y eliminar imagenes relacionadas a novedades
Route::middleware('auth:sanctum')->group(function () {
    Route::post('novedades/{novedad}/imagenes', [ArchivosController::class, 'upload']);
    Route::patch('novedades/{novedad}/imagenes/orden', [ArchivosController::class, 'reorder']);
    Route::delete('novedades/{novedad}/imagenes/{novedadArchivo}', [ArchivosController::class, 'delete']);
});

// Ruta temporal para depuración de novedades
Route::get('novedades-debug/{novedad}', [NovedadController::class, 'debug']);
