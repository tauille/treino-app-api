<?php

namespace App\Http\Controllers;

use App\Models\ExecucaoTreino;
use App\Models\ExecucaoExercicio;
use App\Models\Treino;
use App\Models\Exercicio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExecucaoTreinoController extends Controller
{
    /**
     * Iniciar novo treino
     */
    public function iniciar(Request $request, int $treinoId): JsonResponse
    {
        try {
            $userId = auth()->id();

            // Verificar se o treino existe e pertence ao usuário
            $treino = Treino::ofUser($userId)->with('exerciciosAtivos')->findOrFail($treinoId);

            if ($treino->exerciciosAtivos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este treino não possui exercícios ativos'
                ], 400);
            }

            // Verificar se já existe execução em andamento
            $execucaoEmAndamento = ExecucaoTreino::ofUser($userId)
                ->emAndamento()
                ->first();

            if ($execucaoEmAndamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você já possui um treino em andamento',
                    'data' => [
                        'execucao_atual' => [
                            'id' => $execucaoEmAndamento->id,
                            'treino_nome' => $execucaoEmAndamento->treino->nome_treino,
                            'status' => $execucaoEmAndamento->status,
                        ]
                    ]
                ], 409);
            }

            // Criar nova execução de treino
            $primeiroExercicio = $treino->exerciciosAtivos->first();
            
            $execucaoTreino = ExecucaoTreino::create([
                'user_id' => $userId,
                'treino_id' => $treinoId,
                'status' => 'iniciado',
                'total_exercicios' => $treino->exerciciosAtivos->count(),
                'exercicio_atual_id' => $primeiroExercicio->id,
                'exercicio_atual_ordem' => $primeiroExercicio->ordem,
                'data_inicio' => now(),
            ]);

            // Criar registros dos exercícios a serem executados
            foreach ($treino->exerciciosAtivos as $index => $exercicio) {
                ExecucaoExercicio::criarPorExercicio($execucaoTreino, $exercicio, $index + 1);
            }

            // Iniciar primeiro exercício
            $primeiraExecucaoExercicio = $execucaoTreino->execucaoExercicios()
                ->where('ordem_execucao', 1)
                ->first();
            
            if ($primeiraExecucaoExercicio) {
                $primeiraExecucaoExercicio->iniciar();
            }

            return response()->json([
                'success' => true,
                'message' => 'Treino iniciado com sucesso!',
                'data' => $this->formatarExecucaoCompleta($execucaoTreino)
            ], 201);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treino não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Buscar execução atual em andamento
     */
    public function atual(Request $request): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())
                ->emAndamento()
                ->with(['treino', 'exercicioAtual', 'execucaoExercicios.exercicio'])
                ->first();

            if (!$execucao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum treino em andamento'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatarExecucaoCompleta($execucao)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar execução atual',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Pausar treino
     */
    public function pausar(int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())->findOrFail($execucaoId);

            if (!$execucao->pausar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível pausar este treino'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Treino pausado',
                'data' => ['status' => $execucao->status]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao pausar treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Retomar treino pausado
     */
    public function retomar(int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())->findOrFail($execucaoId);

            if (!$execucao->retomar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível retomar este treino'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Treino retomado',
                'data' => ['status' => $execucao->status]
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao retomar treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Avançar para próximo exercício
     */
    public function proximoExercicio(int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())->findOrFail($execucaoId);

            if (!$execucao->isEmAndamento()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Treino não está em andamento'
                ], 400);
            }

            // Completar exercício atual
            $exercicioAtual = $execucao->execucaoExercicios()
                ->where('exercicio_id', $execucao->exercicio_atual_id)
                ->first();

            if ($exercicioAtual && $exercicioAtual->isEmAndamento()) {
                $exercicioAtual->completar();
                $execucao->increment('exercicios_completados');
            }

            // Avançar para próximo
            if (!$execucao->avancarExercicio()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível avançar'
                ], 400);
            }

            // Se ainda há exercícios, iniciar o próximo
            if ($execucao->status === 'iniciado') {
                $proximoExercicio = $execucao->execucaoExercicios()
                    ->where('exercicio_id', $execucao->exercicio_atual_id)
                    ->first();

                if ($proximoExercicio) {
                    $proximoExercicio->iniciar();
                }
            }

            $execucao->refresh();

            return response()->json([
                'success' => true,
                'message' => $execucao->isFinalizado() ? 'Treino finalizado!' : 'Avançou para próximo exercício',
                'data' => $this->formatarExecucaoCompleta($execucao)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao avançar exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Voltar para exercício anterior
     */
    public function exercicioAnterior(int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())->findOrFail($execucaoId);

            if (!$execucao->isEmAndamento()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Treino não está em andamento'
                ], 400);
            }

            if (!$execucao->voltarExercicio()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível voltar. Você já está no primeiro exercício.'
                ], 400);
            }

            $execucao->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Voltou para exercício anterior',
                'data' => $this->formatarExecucaoCompleta($execucao)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao voltar exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualizar progresso do exercício atual
     */
    public function atualizarExercicio(Request $request, int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())->findOrFail($execucaoId);

            $validatedData = $request->validate([
                'series_realizadas' => 'nullable|integer|min:0',
                'repeticoes_realizadas' => 'nullable|integer|min:0',
                'peso_utilizado' => 'nullable|numeric|min:0',
                'tempo_executado_segundos' => 'nullable|integer|min:0',
                'tempo_descanso_realizado' => 'nullable|integer|min:0',
                'observacoes' => 'nullable|string|max:1000',
            ]);

            $exercicioExecucao = $execucao->execucaoExercicios()
                ->where('exercicio_id', $execucao->exercicio_atual_id)
                ->first();

            if (!$exercicioExecucao) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exercício atual não encontrado'
                ], 404);
            }

            $exercicioExecucao->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Progresso atualizado',
                'data' => $this->formatarExercicioExecucao($exercicioExecucao)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Finalizar treino manualmente
     */
    public function finalizar(Request $request, int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())->findOrFail($execucaoId);

            $validatedData = $request->validate([
                'observacoes' => 'nullable|string|max:1000',
                'tempo_total_segundos' => 'nullable|integer|min:0',
            ]);

            if (!$execucao->finalizar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível finalizar este treino'
                ], 400);
            }

            // Atualizar dados opcionais
            if (!empty($validatedData)) {
                $execucao->update($validatedData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Treino finalizado com sucesso!',
                'data' => $this->formatarExecucaoCompleta($execucao)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Cancelar treino
     */
    public function cancelar(int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())->findOrFail($execucaoId);

            if (!$execucao->cancelar()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível cancelar este treino'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Treino cancelado'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Buscar execução específica
     */
    public function show(int $execucaoId): JsonResponse
    {
        try {
            $execucao = ExecucaoTreino::ofUser(auth()->id())
                ->with(['treino', 'exercicioAtual', 'execucaoExercicios.exercicio'])
                ->findOrFail($execucaoId);

            return response()->json([
                'success' => true,
                'data' => $this->formatarExecucaoCompleta($execucao)
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Execução não encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar execução',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Histórico de execuções
     */
    public function historico(Request $request): JsonResponse
    {
        try {
            $query = ExecucaoTreino::ofUser(auth()->id())
                ->with(['treino'])
                ->orderBy('data_inicio', 'desc');

            // Filtros opcionais
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('treino_id')) {
                $query->where('treino_id', $request->treino_id);
            }

            if ($request->has('data_inicio')) {
                $query->whereDate('data_inicio', $request->data_inicio);
            }

            $execucoes = $query->paginate($request->get('per_page', 15));

            $execucoes->getCollection()->transform(function ($execucao) {
                return [
                    'id' => $execucao->id,
                    'treino' => [
                        'id' => $execucao->treino->id,
                        'nome' => $execucao->treino->nome_treino,
                        'tipo' => $execucao->treino->tipo_treino,
                    ],
                    'status' => $execucao->status,
                    'data_inicio' => $execucao->data_inicio,
                    'data_fim' => $execucao->data_fim,
                    'duracao_formatada' => $execucao->getTempoTotalFormatado(),
                    'progresso_percentual' => $execucao->getProgressoPercentual(),
                    'exercicios_completados' => $execucao->exercicios_completados,
                    'total_exercicios' => $execucao->total_exercicios,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $execucoes,
                'message' => 'Histórico listado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar histórico',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Formatar execução completa para resposta
     */
    private function formatarExecucaoCompleta(ExecucaoTreino $execucao): array
    {
        $execucao->load(['treino', 'exercicioAtual', 'execucaoExercicios.exercicio']);

        return [
            'id' => $execucao->id,
            'status' => $execucao->status,
            'treino' => [
                'id' => $execucao->treino->id,
                'nome' => $execucao->treino->nome_treino,
                'tipo' => $execucao->treino->tipo_treino,
                'dificuldade' => $execucao->treino->dificuldade,
            ],
            'progresso' => [
                'exercicio_atual_ordem' => $execucao->exercicio_atual_ordem,
                'total_exercicios' => $execucao->total_exercicios,
                'exercicios_completados' => $execucao->exercicios_completados,
                'percentual' => $execucao->getProgressoPercentual(),
            ],
            'tempos' => [
                'data_inicio' => $execucao->data_inicio,
                'data_fim' => $execucao->data_fim,
                'tempo_total_segundos' => $execucao->tempo_total_segundos,
                'tempo_total_formatado' => $execucao->getTempoTotalFormatado(),
            ],
            'exercicio_atual' => $execucao->exercicioAtual ? [
                'id' => $execucao->exercicioAtual->id,
                'nome' => $execucao->exercicioAtual->nome_exercicio,
                'grupo_muscular' => $execucao->exercicioAtual->grupo_muscular,
                'tipo_execucao' => $execucao->exercicioAtual->tipo_execucao,
                'series' => $execucao->exercicioAtual->series,
                'repeticoes' => $execucao->exercicioAtual->repeticoes,
                'tempo_execucao' => $execucao->exercicioAtual->tempo_execucao,
                'tempo_descanso' => $execucao->exercicioAtual->tempo_descanso,
                'peso' => $execucao->exercicioAtual->peso,
                'unidade_peso' => $execucao->exercicioAtual->unidade_peso,
                'descricao' => $execucao->exercicioAtual->descricao,
                'observacoes' => $execucao->exercicioAtual->observacoes,
            ] : null,
            'exercicios' => $execucao->execucaoExercicios->map(function ($execucaoExercicio) {
                return $this->formatarExercicioExecucao($execucaoExercicio);
            }),
        ];
    }

    /**
     * Formatar exercício de execução para resposta
     */
    private function formatarExercicioExecucao(ExecucaoExercicio $execucaoExercicio): array
    {
        return [
            'id' => $execucaoExercicio->id,
            'exercicio_id' => $execucaoExercicio->exercicio_id,
            'nome' => $execucaoExercicio->exercicio->nome_exercicio,
            'grupo_muscular' => $execucaoExercicio->exercicio->grupo_muscular,
            'status' => $execucaoExercicio->status,
            'ordem_execucao' => $execucaoExercicio->ordem_execucao,
            'tipo_execucao' => $execucaoExercicio->tipo_execucao,
            'planejado' => [
                'series' => $execucaoExercicio->series_planejadas,
                'repeticoes' => $execucaoExercicio->repeticoes_planejadas,
                'peso' => $execucaoExercicio->peso_planejado,
                'tempo_execucao' => $execucaoExercicio->tempo_execucao_planejado,
                'tempo_descanso' => $execucaoExercicio->tempo_descanso_planejado,
            ],
            'realizado' => [
                'series' => $execucaoExercicio->series_realizadas,
                'repeticoes' => $execucaoExercicio->repeticoes_realizadas,
                'peso' => $execucaoExercicio->peso_utilizado,
                'tempo_executado' => $execucaoExercicio->tempo_executado_segundos,
                'tempo_descanso' => $execucaoExercicio->tempo_descanso_realizado,
            ],
            'tempos' => [
                'data_inicio' => $execucaoExercicio->data_inicio,
                'data_fim' => $execucaoExercicio->data_fim,
                'tempo_formatado' => $execucaoExercicio->getTempoExecutadoFormatado(),
            ],
            'performance' => $execucaoExercicio->getPerformance(),
            'observacoes' => $execucaoExercicio->observacoes,
        ];
    }
}