<?php

namespace App\Http\Controllers;

use App\Models\Exercicio;
use App\Services\EstatisticasService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EstatisticasController extends Controller
{
    private EstatisticasService $estatisticasService;

    public function __construct(EstatisticasService $estatisticasService)
    {
        $this->estatisticasService = $estatisticasService;
    }

    /**
     * Dashboard geral com estatísticas principais
     */
    public function dashboard(): JsonResponse
    {
        try {
            $userId = auth()->id();
            
            $estatisticasGerais = $this->estatisticasService->obterEstatisticasGerais($userId);
            $estatisticas30Dias = $this->estatisticasService->obterEstatisticasPeriodo($userId, 30);
            $favoritos = $this->estatisticasService->obterFavoritos($userId);
            $sequencias = $this->estatisticasService->calcularSequencias($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'geral' => $estatisticasGerais,
                    'ultimos_30_dias' => $estatisticas30Dias,
                    'favoritos' => $favoritos,
                    'sequencias' => $sequencias,
                ],
                'message' => 'Dashboard de estatísticas obtido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dashboard de estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Progresso e evolução por período
     */
    public function progresso(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $periodo = (int) $request->get('periodo', 30);

            $progressoDiario = $this->estatisticasService->obterProgressoDiario($userId, $periodo);
            $topExercicios = $this->estatisticasService->obterTopExercicios($userId, 10);
            $gruposMusculares = $this->estatisticasService->obterEstatisticasGrupoMuscular($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => [
                        'dias' => $periodo,
                        'data_inicio' => now()->subDays($periodo)->format('Y-m-d'),
                        'data_fim' => now()->format('Y-m-d'),
                    ],
                    'progresso_diario' => $progressoDiario,
                    'exercicios_mais_executados' => $topExercicios,
                    'grupos_musculares' => $gruposMusculares,
                ],
                'message' => 'Progresso obtido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter progresso',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Rankings e recordes
     */
    public function rankings(): JsonResponse
    {
        try {
            $userId = auth()->id();

            $topExercicios = $this->estatisticasService->obterTopExercicios($userId, 10);
            $topTreinos = $this->estatisticasService->obterTopTreinos($userId, 10);
            $recordesPeso = $this->estatisticasService->obterRecordesPeso($userId, 10);
            $sequencias = $this->estatisticasService->calcularSequencias($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'top_exercicios' => $topExercicios,
                    'top_treinos' => $topTreinos,
                    'recordes_peso' => $recordesPeso,
                    'sequencias' => $sequencias,
                ],
                'message' => 'Rankings obtidos com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter rankings',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Evolução de um exercício específico
     */
    public function evolucaoExercicio(int $exercicioId): JsonResponse
    {
        try {
            $userId = auth()->id();

            // Verificar se o exercício existe
            $exercicio = Exercicio::findOrFail($exercicioId);

            $evolucao = $this->estatisticasService->obterEvolucaoExercicio($userId, $exercicioId);

            return response()->json([
                'success' => true,
                'data' => [
                    'exercicio' => [
                        'id' => $exercicio->id,
                        'nome' => $exercicio->nome_exercicio,
                        'grupo_muscular' => $exercicio->grupo_muscular,
                        'tipo_execucao' => $exercicio->tipo_execucao,
                    ],
                    'evolucao' => $evolucao,
                ],
                'message' => 'Evolução do exercício obtida com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exercício não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter evolução do exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Estatísticas por grupo muscular
     */
    public function porGrupoMuscular(): JsonResponse
    {
        try {
            $userId = auth()->id();

            $estatisticas = $this->estatisticasService->obterEstatisticasGrupoMuscular($userId);

            return response()->json([
                'success' => true,
                'data' => $estatisticas,
                'message' => 'Estatísticas por grupo muscular obtidas com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas por grupo muscular',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Resumo detalhado por período customizado
     */
    public function resumoPeriodo(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $periodo = (int) $request->get('periodo', 30);

            $estatisticasPeriodo = $this->estatisticasService->obterEstatisticasPeriodo($userId, $periodo);
            $progressoDiario = $this->estatisticasService->obterProgressoDiario($userId, $periodo);

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => $estatisticasPeriodo,
                    'progresso_diario' => $progressoDiario,
                ],
                'message' => 'Resumo do período obtido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter resumo do período',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Métricas de consistência e frequência
     */
    public function metricasConsistencia(): JsonResponse
    {
        try {
            $userId = auth()->id();

            $consistencia = $this->estatisticasService->obterMetricasConsistencia($userId);
            $sequencias = $this->estatisticasService->calcularSequencias($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'consistencia' => $consistencia,
                    'sequencias' => $sequencias,
                ],
                'message' => 'Métricas de consistência obtidas com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter métricas de consistência',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Comparativos entre períodos
     */
    public function comparativos(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $periodo1 = (int) $request->get('periodo1', 30);
            $periodo2 = (int) $request->get('periodo2', 60);

            $estatisticas1 = $this->estatisticasService->obterEstatisticasPeriodo($userId, $periodo1);
            $estatisticas2 = $this->estatisticasService->obterEstatisticasPeriodo($userId, $periodo2);

            // Calcular variações
            $variacao = [
                'treinos' => $estatisticas1['treinos_realizados'] - ($estatisticas2['treinos_realizados'] - $estatisticas1['treinos_realizados']),
                'exercicios' => $estatisticas1['exercicios_realizados'] - ($estatisticas2['exercicios_realizados'] - $estatisticas1['exercicios_realizados']),
                'tempo_total' => $estatisticas1['tempo_total_segundos'] - ($estatisticas2['tempo_total_segundos'] - $estatisticas1['tempo_total_segundos']),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo_atual' => $estatisticas1,
                    'periodo_anterior' => $estatisticas2,
                    'variacao' => $variacao,
                ],
                'message' => 'Comparativo entre períodos obtido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter comparativo',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Progresso em relação a metas
     */
    public function progressoMetas(): JsonResponse
    {
        try {
            $userId = auth()->id();

            $consistencia = $this->estatisticasService->obterMetricasConsistencia($userId);
            $sequencias = $this->estatisticasService->calcularSequencias($userId);

            // Definir metas padrão (podem vir de configuração do usuário)
            $metas = [
                'treinos_semanais' => 3,
                'treinos_mensais' => 12,
                'sequencia_dias' => 7,
                'tempo_semanal_minutos' => 180, // 3 horas
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'metas' => $metas,
                    'progresso' => $consistencia,
                    'sequencias' => $sequencias,
                ],
                'message' => 'Progresso das metas obtido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter progresso das metas',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Exportar dados estatísticos
     */
    public function exportarDados(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $formato = $request->get('formato', 'json'); // json, csv, pdf

            $dados = [
                'geral' => $this->estatisticasService->obterEstatisticasGerais($userId),
                'top_exercicios' => $this->estatisticasService->obterTopExercicios($userId, 20),
                'top_treinos' => $this->estatisticasService->obterTopTreinos($userId, 20),
                'grupos_musculares' => $this->estatisticasService->obterEstatisticasGrupoMuscular($userId),
                'progresso_90_dias' => $this->estatisticasService->obterProgressoDiario($userId, 90),
            ];

            return response()->json([
                'success' => true,
                'data' => $dados,
                'formato' => $formato,
                'gerado_em' => now()->toISOString(),
                'message' => 'Dados exportados com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar dados',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Gerar relatório por tipo
     */
    public function gerarRelatorio(string $tipo): JsonResponse
    {
        try {
            $userId = auth()->id();

            $dados = match($tipo) {
                'semanal' => [
                    'periodo' => $this->estatisticasService->obterEstatisticasPeriodo($userId, 7),
                    'progresso' => $this->estatisticasService->obterProgressoDiario($userId, 7),
                ],
                'mensal' => [
                    'periodo' => $this->estatisticasService->obterEstatisticasPeriodo($userId, 30),
                    'progresso' => $this->estatisticasService->obterProgressoDiario($userId, 30),
                    'top_exercicios' => $this->estatisticasService->obterTopExercicios($userId, 10),
                ],
                'anual' => [
                    'periodo' => $this->estatisticasService->obterEstatisticasPeriodo($userId, 365),
                    'geral' => $this->estatisticasService->obterEstatisticasGerais($userId),
                    'rankings' => [
                        'exercicios' => $this->estatisticasService->obterTopExercicios($userId, 15),
                        'treinos' => $this->estatisticasService->obterTopTreinos($userId, 15),
                        'recordes' => $this->estatisticasService->obterRecordesPeso($userId, 15),
                    ],
                ],
            };

            return response()->json([
                'success' => true,
                'data' => $dados,
                'tipo' => $tipo,
                'gerado_em' => now()->toISOString(),
                'message' => "Relatório {$tipo} gerado com sucesso"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    // ========== MÉTODOS DE ANALYTICS ESPECÍFICOS ==========

    /**
     * Frequência semanal de treinos
     */
    public function frequenciaSemanal(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $progresso = $this->estatisticasService->obterProgressoDiario($userId, 28);

            // Agrupar por dia da semana
            $frequencia = $progresso->groupBy('dia_semana')->map(function ($dias) {
                return [
                    'total_treinos' => $dias->sum('total_treinos'),
                    'media_treinos' => $dias->avg('total_treinos'),
                    'total_tempo' => $dias->sum('tempo_total'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $frequencia,
                'message' => 'Frequência semanal obtida com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter frequência semanal',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Horários preferidos para treino
     */
    public function horariosPreferidos(): JsonResponse
    {
        try {
            $userId = auth()->id();
            // Este seria implementado com análise dos horários de criação das execuções
            
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Funcionalidade em desenvolvimento',
                    'sugestao' => 'Adicionar campo hora_inicio nas execuções para análise detalhada'
                ],
                'message' => 'Horários preferidos - em desenvolvimento'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter horários preferidos',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Duração média por tipo de treino
     */
    public function duracaoMedia(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $estatisticasGerais = $this->estatisticasService->obterEstatisticasGerais($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'duracao_media_geral' => $estatisticasGerais['media_duracao_formatada'],
                    'tempo_total' => $estatisticasGerais['tempo_total_formatado'],
                ],
                'message' => 'Duração média obtida com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter duração média',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Evolução de peso para exercício específico
     */
    public function evolucaoPesoExercicio(int $exercicioId): JsonResponse
    {
        try {
            $userId = auth()->id();
            $evolucao = $this->estatisticasService->obterEvolucaoExercicio($userId, $exercicioId);

            return response()->json([
                'success' => true,
                'data' => $evolucao,
                'message' => 'Evolução de peso obtida com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter evolução de peso',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Volume total de treino (peso x repetições)
     */
    public function volumeTreino(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $gruposMusculares = $this->estatisticasService->obterEstatisticasGrupoMuscular($userId);

            return response()->json([
                'success' => true,
                'data' => $gruposMusculares,
                'message' => 'Volume de treino obtido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter volume de treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}