<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TreinoController;
use App\Http\Controllers\ExercicioController;
use App\Http\Controllers\ExecucaoTreinoController;
use App\Http\Controllers\EstatisticasController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Rotas públicas (sem autenticação)
|--------------------------------------------------------------------------
*/

// Rota de status da API
Route::get('/status', function () {
    return response()->json([
        'status' => 'online',
        'message' => 'Treino App API está funcionando!',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString()
    ]);
});

// Rota para verificar saúde da aplicação
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => 'connected',
        'api' => 'operational',
        'timestamp' => now()->toISOString()
    ]);
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    // Autenticação tradicional
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Autenticação com Google
    Route::post('/google', [GoogleAuthController::class, 'handleGoogleAuth']);
    
    // Rotas protegidas de autenticação
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
        Route::get('/verify-token', [AuthController::class, 'verifyToken']);
        
        // Google específico
        Route::delete('/google/disconnect', [GoogleAuthController::class, 'disconnect']);
        Route::get('/google/status', [GoogleAuthController::class, 'status']);
    });
});

/*
|--------------------------------------------------------------------------
| Rotas protegidas por autenticação
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    
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
    
    /*
    |--------------------------------------------------------------------------
    | EXECUÇÃO DE TREINOS - Sistema completo
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('execucao')->name('execucao.')->group(function () {
        // Gerenciamento de execuções
        Route::post('treinos/{treino}/iniciar', [ExecucaoTreinoController::class, 'iniciarTreino'])
            ->name('iniciar');
        
        Route::get('atual', [ExecucaoTreinoController::class, 'obterExecucaoAtual'])
            ->name('atual');
        
        Route::get('{execucao}', [ExecucaoTreinoController::class, 'show'])
            ->name('show');
        
        // Controles de execução
        Route::put('{execucao}/pausar', [ExecucaoTreinoController::class, 'pausarTreino'])
            ->name('pausar');
        
        Route::put('{execucao}/retomar', [ExecucaoTreinoController::class, 'retomarTreino'])
            ->name('retomar');
        
        Route::put('{execucao}/proximo-exercicio', [ExecucaoTreinoController::class, 'proximoExercicio'])
            ->name('proximo-exercicio');
        
        Route::put('{execucao}/exercicio-anterior', [ExecucaoTreinoController::class, 'exercicioAnterior'])
            ->name('exercicio-anterior');
        
        Route::put('{execucao}/atualizar-exercicio', [ExecucaoTreinoController::class, 'atualizarExercicio'])
            ->name('atualizar-exercicio');
        
        Route::put('{execucao}/finalizar', [ExecucaoTreinoController::class, 'finalizarTreino'])
            ->name('finalizar');
        
        Route::delete('{execucao}/cancelar', [ExecucaoTreinoController::class, 'cancelarTreino'])
            ->name('cancelar');
        
        // Histórico e listagem
        Route::get('historico', [ExecucaoTreinoController::class, 'historico'])
            ->name('historico');
        
        Route::get('em-andamento', [ExecucaoTreinoController::class, 'execucoesEmAndamento'])
            ->name('em-andamento');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ESTATÍSTICAS - Sistema completo de métricas
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('estatisticas')->name('estatisticas.')->group(function () {
        
        // Dashboard principal com visão geral
        Route::get('dashboard', [EstatisticasController::class, 'dashboard'])
            ->name('dashboard');
        
        // Progresso e evolução temporal
        Route::get('progresso', [EstatisticasController::class, 'progresso'])
            ->name('progresso');
        
        // Rankings e recordes
        Route::get('rankings', [EstatisticasController::class, 'rankings'])
            ->name('rankings');
        
        // Estatísticas por grupo muscular
        Route::get('grupos-musculares', [EstatisticasController::class, 'porGrupoMuscular'])
            ->name('grupos-musculares');
        
        // Evolução específica de exercício
        Route::get('exercicio/{exercicio}/evolucao', [EstatisticasController::class, 'evolucaoExercicio'])
            ->name('exercicio.evolucao');
        
        // Endpoints detalhados adicionais
        Route::get('resumo-periodo', [EstatisticasController::class, 'resumoPeriodo'])
            ->name('resumo-periodo');
        
        Route::get('consistencia', [EstatisticasController::class, 'metricas Consistencia'])
            ->name('consistencia');
        
        Route::get('comparativos', [EstatisticasController::class, 'comparativos'])
            ->name('comparativos');
        
        Route::get('metas', [EstatisticasController::class, 'progressoMetas'])
            ->name('metas');
        
        // Exportação de dados
        Route::get('exportar', [EstatisticasController::class, 'exportarDados'])
            ->name('exportar');
        
        Route::get('relatorio/{tipo}', [EstatisticasController::class, 'gerarRelatorio'])
            ->name('relatorio')
            ->where('tipo', 'mensal|semanal|anual');
        
        // Analytics específicos
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('frequencia-semanal', [EstatisticasController::class, 'frequenciaSemanal'])
                ->name('frequencia-semanal');
            
            Route::get('horarios-preferidos', [EstatisticasController::class, 'horariosPreferidos'])
                ->name('horarios-preferidos');
            
            Route::get('duracao-media', [EstatisticasController::class, 'duracaoMedia'])
                ->name('duracao-media');
            
            Route::get('evolucao-peso/{exercicio}', [EstatisticasController::class, 'evolucaoPesoExercicio'])
                ->name('evolucao-peso');
            
            Route::get('volume-treino', [EstatisticasController::class, 'volumeTreino'])
                ->name('volume-treino');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | Rota do usuário autenticado
    |--------------------------------------------------------------------------
    */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

/*
|--------------------------------------------------------------------------
| Rota de fallback para rotas não encontradas
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint não encontrado',
        'available_endpoints' => [
            'GET /api/status' => 'Status da API',
            'GET /api/health' => 'Saúde da aplicação',
            'POST /api/auth/login' => 'Login de usuário',
            'GET /api/treinos' => 'Listar treinos (requer auth)',
            'GET /api/execucao/atual' => 'Execução atual (requer auth)',
            'GET /api/estatisticas/dashboard' => 'Dashboard de estatísticas (requer auth)',
        ]
    ], 404);
});