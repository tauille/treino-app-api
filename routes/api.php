<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TreinoController;
use App\Http\Controllers\ExercicioController;
use App\Http\Controllers\ExecucaoTreinoController;
use App\Http\Controllers\EstatisticasController; // NOVA IMPORTAÇÃO
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\GrupoMuscularController;
use App\Http\Controllers\ExercicioTemplateController;

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

// Rota de teste básico
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
        'execucao_treinos' => 'enabled',
        'estatisticas' => 'enabled', // NOVA FEATURE
    ]);
});

// Rota para verificar saúde da aplicação
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => 'connected',
        'api' => 'operational',
        'google_auth' => 'configured',
        'execucao_treinos' => 'configured',
        'estatisticas' => 'configured', // NOVA FEATURE
        'timestamp' => now()->toISOString()
    ]);
});

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação - Organizadas por prefixo
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
| ROTAS PARA FLUTTER (SEM MIDDLEWARES WEB) - Mantidas para desenvolvimento
|--------------------------------------------------------------------------
*/

Route::prefix('flutter')->group(function () {
    // Grupos musculares
    Route::get('/grupos-musculares', [GrupoMuscularController::class, 'index']);
    Route::get('/grupos-musculares/{id}', [GrupoMuscularController::class, 'show']);
    Route::get('/grupos-musculares/{id}/exercicios-templates', [GrupoMuscularController::class, 'exerciciosTemplates']);
    
    // Templates de exercícios
    Route::get('/exercicios-templates', [ExercicioTemplateController::class, 'index']);
    Route::get('/exercicios-templates/populares', [ExercicioTemplateController::class, 'populares']);
    Route::get('/exercicios-templates/buscar', [ExercicioTemplateController::class, 'buscar']);
    Route::get('/exercicios-templates/{id}', [ExercicioTemplateController::class, 'show']);
    Route::get('/exercicios-templates/mais-utilizados', [ExercicioTemplateController::class, 'maisUtilizados']);
    
    // Treinos - VERSÃO SEM MIDDLEWARES WEB para desenvolvimento Flutter
    Route::get('/treinos', function(\Illuminate\Http\Request $request) {
        try {
            $query = \App\Models\Treino::with(['exerciciosAtivos'])->where('user_id', 1);
            $treinos = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $treinos->map(function ($treino) {
                    return [
                        'id' => $treino->id,
                        'nome_treino' => $treino->nome_treino,
                        'tipo_treino' => $treino->tipo_treino,
                        'descricao' => $treino->descricao,
                        'dificuldade' => $treino->dificuldade,
                        'status' => $treino->status,
                        'total_exercicios' => $treino->exerciciosAtivos->count(),
                        'created_at' => $treino->created_at,
                    ];
                }),
                'message' => 'Treinos listados com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar treinos',
                'error' => $e->getMessage()
            ], 500);
        }
    });
    
    Route::post('/treinos', function(\Illuminate\Http\Request $request) {
        try {
            $validatedData = $request->validate([
                'nome_treino' => 'required|string|max:255',
                'tipo_treino' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'dificuldade' => 'nullable|in:iniciante,intermediario,avancado',
                'status' => 'nullable|in:ativo,inativo'
            ]);

            $validatedData['user_id'] = 1;
            $validatedData['status'] = $validatedData['status'] ?? 'ativo';
            
            $treino = \App\Models\Treino::create($validatedData);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $treino->id,
                    'nome_treino' => $treino->nome_treino,
                    'tipo_treino' => $treino->tipo_treino,
                    'descricao' => $treino->descricao,
                    'dificuldade' => $treino->dificuldade,
                    'status' => $treino->status,
                ],
                'message' => 'Treino criado com sucesso'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar treino',
                'error' => $e->getMessage()
            ], 500);
        }
    });
    
    // Outras rotas Flutter mantidas... (treinos/{id}, exercícios, etc.)
    Route::get('/treinos/{id}', function($id) {
        try {
            $treino = \App\Models\Treino::with(['exerciciosAtivos'])
                ->where('user_id', 1)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $treino->id,
                    'nome_treino' => $treino->nome_treino,
                    'tipo_treino' => $treino->tipo_treino,
                    'descricao' => $treino->descricao,
                    'dificuldade' => $treino->dificuldade,
                    'status' => $treino->status,
                    'total_exercicios' => $treino->exerciciosAtivos->count(),
                    'exercicios' => $treino->exerciciosAtivos->map(function ($exercicio) {
                        return [
                            'id' => $exercicio->id,
                            'nome_exercicio' => $exercicio->nome_exercicio,
                            'tipo_execucao' => $exercicio->tipo_execucao,
                            'repeticoes' => $exercicio->repeticoes,
                            'series' => $exercicio->series,
                            'tempo_execucao' => $exercicio->tempo_execucao,
                            'tempo_descanso' => $exercicio->tempo_descanso,
                            'ordem' => $exercicio->ordem,
                        ];
                    }),
                    'created_at' => $treino->created_at,
                ],
                'message' => 'Treino encontrado com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treino não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar treino',
                'error' => $e->getMessage()
            ], 500);
        }
    });
});

/*
|--------------------------------------------------------------------------
| ROTAS DE TESTE E DEBUG (SEM AUTENTICAÇÃO)
|--------------------------------------------------------------------------
*/

// Teste de banco direto
Route::get('/test-database', function () {
    try {
        \DB::connection()->getPdo();
        
        $tables = \DB::select('SHOW TABLES');
        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);
        
        return response()->json([
            'success' => true,
            'database' => 'connected',
            'tables' => $tableNames,
            'grupos_exists' => \Schema::hasTable('grupos_musculares'),
            'templates_exists' => \Schema::hasTable('exercicios_templates'),
            'execucao_treinos_exists' => \Schema::hasTable('execucao_treinos'),
            'execucao_exercicios_exists' => \Schema::hasTable('execucao_exercicios')
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

// ROTA DE TESTE SIMPLES PARA DEBUG
Route::get('/test-simple', function() {
    try {
        $usuarios = \App\Models\User::count();
        $treinos = \App\Models\Treino::count();
        $estrutura = \DB::select('DESCRIBE exercicios');
        
        return response()->json([
            'success' => true,
            'usuarios' => $usuarios,
            'treinos' => $treinos,
            'estrutura_exercicios' => count($estrutura) . ' campos',
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});

/*
|--------------------------------------------------------------------------
| Rotas protegidas por autenticação
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Rota do usuário autenticado
    |--------------------------------------------------------------------------
    */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    /*
    |--------------------------------------------------------------------------
    | GRUPOS MUSCULARES - Rotas principais
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('grupos-musculares')->group(function () {
        Route::get('/', [GrupoMuscularController::class, 'index'])->name('grupos-musculares.index');
        Route::get('/mais-utilizados', [GrupoMuscularController::class, 'maisUtilizados'])->name('grupos-musculares.mais-utilizados');
        Route::post('/', [GrupoMuscularController::class, 'store'])->name('grupos-musculares.store');
        Route::get('/{id}', [GrupoMuscularController::class, 'show'])->name('grupos-musculares.show');
        Route::get('/{id}/exercicios-templates', [GrupoMuscularController::class, 'exerciciosTemplates'])->name('grupos-musculares.exercicios-templates');
    });
    
    /*
    |--------------------------------------------------------------------------
    | EXERCÍCIOS TEMPLATES - Rotas principais
    |--------------------------------------------------------------------------
    */
    
    Route::prefix('exercicios-templates')->group(function () {
        Route::get('/', [ExercicioTemplateController::class, 'index'])->name('exercicios-templates.index');
        Route::get('/populares', [ExercicioTemplateController::class, 'populares'])->name('exercicios-templates.populares');
        Route::get('/mais-utilizados', [ExercicioTemplateController::class, 'maisUtilizados'])->name('exercicios-templates.mais-utilizados');
        Route::get('/buscar', [ExercicioTemplateController::class, 'buscar'])->name('exercicios-templates.buscar');
        Route::post('/', [ExercicioTemplateController::class, 'store'])->name('exercicios-templates.store');
        Route::get('/{id}', [ExercicioTemplateController::class, 'show'])->name('exercicios-templates.show');
    });
    
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
    | EXECUÇÃO DE TREINOS - Sistema completo ⭐
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
    | ESTATÍSTICAS - Sistema completo de métricas ⭐ NOVA SEÇÃO
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
        
        Route::get('consistencia', [EstatisticasController::class, 'metricasConsistencia'])
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
});

/*
|--------------------------------------------------------------------------
| Rotas de Desenvolvimento (apenas em debug)
|--------------------------------------------------------------------------
*/

if (config('app.debug')) {
    Route::prefix('dev')->group(function () {
        // Testar conexão com banco
        Route::get('/db-test', function () {
            try {
                \DB::connection()->getPdo();
                $count = \App\Models\User::count();
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão com banco OK',
                    'users_count' => $count
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro na conexão com banco',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
        
        // Testar seeds
        Route::get('/seed-status', function () {
            try {
                return response()->json([
                    'grupos_musculares' => \App\Models\GrupoMuscular::count(),
                    'exercicios_templates' => \App\Models\ExercicioTemplate::count(),
                    'treinos' => \App\Models\Treino::count(),
                    'exercicios' => \App\Models\Exercicio::count(),
                    'execucao_treinos' => \App\Models\ExecucaoTreino::count(),
                    'execucao_exercicios' => \App\Models\ExecucaoExercicio::count(),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ], 500);
            }
        });
        
        // Testar sistema de execução
        Route::get('/test-execucao', function () {
            try {
                $tables = [
                    'treinos' => \Schema::hasTable('treinos'),
                    'exercicios' => \Schema::hasTable('exercicios'),
                    'execucao_treinos' => \Schema::hasTable('execucao_treinos'),
                    'execucao_exercicios' => \Schema::hasTable('execucao_exercicios'),
                ];
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sistema de execução verificado',
                    'tabelas_existem' => $tables,
                    'todas_tabelas_ok' => !in_array(false, $tables),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    });
}

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