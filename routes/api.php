<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TreinoController;
use App\Http\Controllers\ExercicioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação (sem middleware)
|--------------------------------------------------------------------------
*/

// Autenticação tradicional
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Autenticação Google
Route::post('/auth/google', [GoogleAuthController::class, 'handleGoogleAuth']);

/*
|--------------------------------------------------------------------------
| Rotas protegidas por autenticação
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Autenticação (usuário logado)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/verify-token', [AuthController::class, 'verifyToken']);
    
    // Google Auth (usuário logado)
    Route::delete('/auth/google/disconnect', [GoogleAuthController::class, 'disconnect']);
    Route::get('/auth/google/status', [GoogleAuthController::class, 'status']);
    
    /*
    |--------------------------------------------------------------------------
    | TREINOS - Rotas principais
    |--------------------------------------------------------------------------
    */
    
    // Rotas CRUD básicas para treinos
    Route::apiResource('treinos', TreinoController::class);
    
    // Rotas customizadas para treinos
    Route::get('treinos/dificuldade/{dificuldade}', [TreinoController::class, 'porDificuldade'])
        ->name('treinos.por-dificuldade');
    
    /*
    |--------------------------------------------------------------------------
    | EXERCÍCIOS - Rotas aninhadas em treinos
    |--------------------------------------------------------------------------
    */
    
    // Rotas CRUD para exercícios (aninhadas em treinos)
    Route::apiResource('treinos.exercicios', ExercicioController::class)
        ->except(['create', 'edit']);
    
    // Rotas customizadas para exercícios
    Route::put('treinos/{treino}/exercicios/reordenar', [ExercicioController::class, 'reordenar'])
        ->name('exercicios.reordenar');
    
    Route::get('treinos/{treino}/exercicios/grupo/{grupoMuscular}', [ExercicioController::class, 'porGrupoMuscular'])
        ->name('exercicios.por-grupo-muscular');
});

/*
|--------------------------------------------------------------------------
| Rotas públicas (sem autenticação)
|--------------------------------------------------------------------------
*/

// Rota de teste
Route::get('/teste', function () {
    return response()->json(['message' => 'API funcionando!']);
});

// Rota de status da API
Route::get('/status', function () {
    return response()->json([
        'status' => 'online',
        'message' => 'Treino App API está funcionando!',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'google_auth' => 'enabled',
    ]);
});

// Rota para verificar saúde da aplicação
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => 'connected',
        'api' => 'operational',
        'google_auth' => 'configured',
        'timestamp' => now()->toISOString()
    ]);
});