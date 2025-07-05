<?php

namespace App\Http\Controllers;

use App\Models\Treino;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TreinoController extends Controller
{
    /**
     * Lista todos os treinos do usuário autenticado
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Treino::with(['exerciciosAtivos'])
                ->ofUser(auth()->id())
                ->ativo();

            // Filtros opcionais
            if ($request->has('busca')) {
                $query->busca($request->busca);
            }

            if ($request->has('dificuldade')) {
                $query->dificuldade($request->dificuldade);
            }

            if ($request->has('tipo_treino')) {
                $query->ofType($request->tipo_treino);
            }

            // Ordenação
            $orderBy = $request->get('order_by', 'created_at');
            $orderDirection = $request->get('order_direction', 'desc');
            $query->orderBy($orderBy, $orderDirection);

            $treinos = $query->paginate($request->get('per_page', 15));

            // Adicionar informações calculadas
            $treinos->getCollection()->transform(function ($treino) {
                return [
                    'id' => $treino->id,
                    'nome_treino' => $treino->nome_treino,
                    'tipo_treino' => $treino->tipo_treino,
                    'descricao' => $treino->descricao,
                    'dificuldade' => $treino->dificuldade,
                    'dificuldade_texto' => $treino->dificuldade_texto,
                    'cor_dificuldade' => $treino->cor_dificuldade,
                    'status' => $treino->status,
                    'total_exercicios' => $treino->total_exercicios_ativos,
                    'duracao_estimada' => $treino->duracao_estimada,
                    'duracao_formatada' => $treino->duracao_formatada,
                    'grupos_musculares' => $treino->grupos_musculares_texto,
                    'created_at' => $treino->created_at,
                    'updated_at' => $treino->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $treinos,
                'message' => 'Treinos listados com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar treinos',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Exibe um treino específico com seus exercícios
     */
    public function show(int $id): JsonResponse
    {
        try {
            $treino = Treino::with(['exerciciosAtivos'])
                ->ofUser(auth()->id())
                ->findOrFail($id);

            $treinoData = [
                'id' => $treino->id,
                'nome_treino' => $treino->nome_treino,
                'tipo_treino' => $treino->tipo_treino,
                'descricao' => $treino->descricao,
                'dificuldade' => $treino->dificuldade,
                'dificuldade_texto' => $treino->dificuldade_texto,
                'cor_dificuldade' => $treino->cor_dificuldade,
                'status' => $treino->status,
                'total_exercicios' => $treino->total_exercicios_ativos,
                'duracao_estimada' => $treino->duracao_estimada,
                'duracao_formatada' => $treino->duracao_formatada,
                'grupos_musculares' => $treino->grupos_musculares_texto,
                'exercicios' => $treino->exerciciosAtivos->map(function ($exercicio) {
                    return [
                        'id' => $exercicio->id,
                        'nome_exercicio' => $exercicio->nome_exercicio,
                        'descricao' => $exercicio->descricao,
                        'grupo_muscular' => $exercicio->grupo_muscular,
                        'tipo_execucao' => $exercicio->tipo_execucao,
                        'repeticoes' => $exercicio->repeticoes,
                        'series' => $exercicio->series,
                        'tempo_execucao' => $exercicio->tempo_execucao,
                        'tempo_descanso' => $exercicio->tempo_descanso,
                        'peso' => $exercicio->peso,
                        'unidade_peso' => $exercicio->unidade_peso,
                        'ordem' => $exercicio->ordem,
                        'observacoes' => $exercicio->observacoes,
                        'texto_execucao' => $exercicio->texto_execucao,
                        'texto_descanso' => $exercicio->texto_descanso,
                        'tempo_total_estimado' => $exercicio->tempo_total_estimado,
                        'imagem_url' => $exercicio->imagem_url,
                    ];
                }),
                'created_at' => $treino->created_at,
                'updated_at' => $treino->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $treinoData,
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
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Cria um novo treino
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'nome_treino' => 'required|string|max:255',
                'tipo_treino' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'dificuldade' => 'nullable|in:iniciante,intermediario,avancado',
                'status' => 'nullable|in:ativo,inativo'
            ]);

            $validatedData['user_id'] = auth()->id();

            $treino = Treino::create($validatedData);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $treino->id,
                    'nome_treino' => $treino->nome_treino,
                    'tipo_treino' => $treino->tipo_treino,
                    'descricao' => $treino->descricao,
                    'dificuldade' => $treino->dificuldade,
                    'dificuldade_texto' => $treino->dificuldade_texto,
                    'status' => $treino->status,
                ],
                'message' => 'Treino criado com sucesso'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualiza um treino existente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $treino = Treino::ofUser(auth()->id())->findOrFail($id);

            $validatedData = $request->validate([
                'nome_treino' => 'sometimes|required|string|max:255',
                'tipo_treino' => 'sometimes|required|string|max:255',
                'descricao' => 'nullable|string',
                'dificuldade' => 'nullable|in:iniciante,intermediario,avancado',
                'status' => 'nullable|in:ativo,inativo'
            ]);

            $treino->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $treino->id,
                    'nome_treino' => $treino->nome_treino,
                    'tipo_treino' => $treino->tipo_treino,
                    'descricao' => $treino->descricao,
                    'dificuldade' => $treino->dificuldade,
                    'dificuldade_texto' => $treino->dificuldade_texto,
                    'status' => $treino->status,
                ],
                'message' => 'Treino atualizado com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treino não encontrado'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Remove um treino (soft delete - marca como inativo)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $treino = Treino::ofUser(auth()->id())->findOrFail($id);

            // Soft delete - marca como inativo em vez de deletar
            $treino->update(['status' => 'inativo']);

            return response()->json([
                'success' => true,
                'message' => 'Treino removido com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treino não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover treino',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista treinos por dificuldade
     */
    public function porDificuldade(string $dificuldade): JsonResponse
    {
        try {
            $treinos = Treino::with(['exerciciosAtivos'])
                ->ofUser(auth()->id())
                ->ativo()
                ->dificuldade($dificuldade)
                ->orderBy('nome_treino')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $treinos->map(function ($treino) {
                    return [
                        'id' => $treino->id,
                        'nome_treino' => $treino->nome_treino,
                        'tipo_treino' => $treino->tipo_treino,
                        'duracao_formatada' => $treino->duracao_formatada,
                        'total_exercicios' => $treino->total_exercicios_ativos,
                    ];
                }),
                'message' => "Treinos de nível {$dificuldade} listados com sucesso"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar treinos por dificuldade',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}