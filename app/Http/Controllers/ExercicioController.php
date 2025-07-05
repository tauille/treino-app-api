<?php

namespace App\Http\Controllers;

use App\Models\Exercicio;
use App\Models\Treino;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ExercicioController extends Controller
{
    /**
     * Lista todos os exercícios de um treino específico
     */
    public function index(int $treinoId): JsonResponse
    {
        try {
            // Verificar se o treino pertence ao usuário autenticado
            $treino = Treino::ofUser(auth()->id())->findOrFail($treinoId);

            $exercicios = Exercicio::ofTreino($treinoId)
                ->ativo()
                ->ordered()
                ->get();

            $exerciciosData = $exercicios->map(function ($exercicio) {
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
                    'status' => $exercicio->status,
                    'texto_execucao' => $exercicio->texto_execucao,
                    'texto_descanso' => $exercicio->texto_descanso,
                    'tempo_total_estimado' => $exercicio->tempo_total_estimado,
                    'imagem_url' => $exercicio->imagem_url,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'treino' => [
                        'id' => $treino->id,
                        'nome_treino' => $treino->nome_treino,
                        'total_exercicios' => $exercicios->count(),
                    ],
                    'exercicios' => $exerciciosData
                ],
                'message' => 'Exercícios listados com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treino não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar exercícios',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Exibe um exercício específico
     */
    public function show(int $treinoId, int $exercicioId): JsonResponse
    {
        try {
            // Verificar se o treino pertence ao usuário autenticado
            $treino = Treino::ofUser(auth()->id())->findOrFail($treinoId);

            $exercicio = Exercicio::where('treino_id', $treinoId)
                ->findOrFail($exercicioId);

            $exercicioData = [
                'id' => $exercicio->id,
                'treino_id' => $exercicio->treino_id,
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
                'status' => $exercicio->status,
                'texto_execucao' => $exercicio->texto_execucao,
                'texto_descanso' => $exercicio->texto_descanso,
                'tempo_total_estimado' => $exercicio->tempo_total_estimado,
                'imagem_url' => $exercicio->imagem_url,
                'created_at' => $exercicio->created_at,
                'updated_at' => $exercicio->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $exercicioData,
                'message' => 'Exercício encontrado com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exercício não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Cria um novo exercício para um treino
     */
    public function store(Request $request, int $treinoId): JsonResponse
    {
        try {
            // Verificar se o treino pertence ao usuário autenticado
            $treino = Treino::ofUser(auth()->id())->findOrFail($treinoId);

            $validatedData = $request->validate([
                'nome_exercicio' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'grupo_muscular' => 'nullable|string|max:255',
                'tipo_execucao' => 'required|in:repeticao,tempo',
                'repeticoes' => 'nullable|integer|min:1|required_if:tipo_execucao,repeticao',
                'series' => 'nullable|integer|min:1',
                'tempo_execucao' => 'nullable|integer|min:1|required_if:tipo_execucao,tempo',
                'tempo_descanso' => 'nullable|integer|min:0',
                'peso' => 'nullable|numeric|min:0',
                'unidade_peso' => 'nullable|string|max:10',
                'ordem' => 'nullable|integer|min:1',
                'observacoes' => 'nullable|string',
                'status' => 'nullable|in:ativo,inativo',
                'imagem_path' => 'nullable|string|max:500',
            ]);

            $validatedData['treino_id'] = $treinoId;

            $exercicio = Exercicio::create($validatedData);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $exercicio->id,
                    'nome_exercicio' => $exercicio->nome_exercicio,
                    'grupo_muscular' => $exercicio->grupo_muscular,
                    'tipo_execucao' => $exercicio->tipo_execucao,
                    'texto_execucao' => $exercicio->texto_execucao,
                    'ordem' => $exercicio->ordem,
                    'status' => $exercicio->status,
                ],
                'message' => 'Exercício criado com sucesso'
            ], 201);

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
                'message' => 'Erro ao criar exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Atualiza um exercício existente
     */
    public function update(Request $request, int $treinoId, int $exercicioId): JsonResponse
    {
        try {
            // Verificar se o treino pertence ao usuário autenticado
            $treino = Treino::ofUser(auth()->id())->findOrFail($treinoId);

            $exercicio = Exercicio::where('treino_id', $treinoId)
                ->findOrFail($exercicioId);

            $validatedData = $request->validate([
                'nome_exercicio' => 'sometimes|required|string|max:255',
                'descricao' => 'nullable|string',
                'grupo_muscular' => 'nullable|string|max:255',
                'tipo_execucao' => 'sometimes|required|in:repeticao,tempo',
                'repeticoes' => 'nullable|integer|min:1',
                'series' => 'nullable|integer|min:1',
                'tempo_execucao' => 'nullable|integer|min:1',
                'tempo_descanso' => 'nullable|integer|min:0',
                'peso' => 'nullable|numeric|min:0',
                'unidade_peso' => 'nullable|string|max:10',
                'ordem' => 'nullable|integer|min:1',
                'observacoes' => 'nullable|string',
                'status' => 'nullable|in:ativo,inativo',
                'imagem_path' => 'nullable|string|max:500',
            ]);

            $exercicio->update($validatedData);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $exercicio->id,
                    'nome_exercicio' => $exercicio->nome_exercicio,
                    'grupo_muscular' => $exercicio->grupo_muscular,
                    'tipo_execucao' => $exercicio->tipo_execucao,
                    'texto_execucao' => $exercicio->texto_execucao,
                    'ordem' => $exercicio->ordem,
                    'status' => $exercicio->status,
                ],
                'message' => 'Exercício atualizado com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exercício não encontrado'
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
                'message' => 'Erro ao atualizar exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Remove um exercício (soft delete - marca como inativo)
     */
    public function destroy(int $treinoId, int $exercicioId): JsonResponse
    {
        try {
            // Verificar se o treino pertence ao usuário autenticado
            $treino = Treino::ofUser(auth()->id())->findOrFail($treinoId);

            $exercicio = Exercicio::where('treino_id', $treinoId)
                ->findOrFail($exercicioId);

            // Soft delete - marca como inativo em vez de deletar
            $exercicio->update(['status' => 'inativo']);

            return response()->json([
                'success' => true,
                'message' => 'Exercício removido com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exercício não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover exercício',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Reordena exercícios de um treino
     */
    public function reordenar(Request $request, int $treinoId): JsonResponse
    {
        try {
            // Verificar se o treino pertence ao usuário autenticado
            $treino = Treino::ofUser(auth()->id())->findOrFail($treinoId);

            $validatedData = $request->validate([
                'exercicios' => 'required|array',
                'exercicios.*.id' => 'required|integer|exists:exercicios,id',
                'exercicios.*.ordem' => 'required|integer|min:1',
            ]);

            foreach ($validatedData['exercicios'] as $exercicioData) {
                Exercicio::where('id', $exercicioData['id'])
                    ->where('treino_id', $treinoId)
                    ->update(['ordem' => $exercicioData['ordem']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Exercícios reordenados com sucesso'
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
                'message' => 'Erro ao reordenar exercícios',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista exercícios por grupo muscular
     */
    public function porGrupoMuscular(int $treinoId, string $grupoMuscular): JsonResponse
    {
        try {
            // Verificar se o treino pertence ao usuário autenticado
            $treino = Treino::ofUser(auth()->id())->findOrFail($treinoId);

            $exercicios = Exercicio::ofTreino($treinoId)
                ->ativo()
                ->grupoMuscular($grupoMuscular)
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $exercicios->map(function ($exercicio) {
                    return [
                        'id' => $exercicio->id,
                        'nome_exercicio' => $exercicio->nome_exercicio,
                        'texto_execucao' => $exercicio->texto_execucao,
                        'ordem' => $exercicio->ordem,
                    ];
                }),
                'message' => "Exercícios do grupo {$grupoMuscular} listados com sucesso"
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treino não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar exercícios por grupo muscular',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}