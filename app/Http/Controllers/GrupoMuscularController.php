<?php

namespace App\Http\Controllers;

use App\Models\GrupoMuscular;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GrupoMuscularController extends Controller
{
    /**
     * Lista todos os grupos musculares
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = GrupoMuscular::ativo()->ordenado();

            // Filtro de busca
            if ($request->has('busca')) {
                $query->busca($request->busca);
            }

            // Incluir estatísticas se solicitado
            if ($request->get('com_stats', false)) {
                $query->withCount([
                    'exerciciosTemplates as total_templates' => function($q) {
                        $q->where('ativo', true);
                    },
                    'exerciciosTemplates as templates_populares' => function($q) {
                        $q->where('ativo', true)->where('popular', true);
                    }
                ]);
            }

            $grupos = $query->get();

            return response()->json([
                'success' => true,
                'data' => $grupos->map(function ($grupo) use ($request) {
                    $data = [
                        'id' => $grupo->id,
                        'nome' => $grupo->nome,
                        'slug' => $grupo->slug,
                        'icone' => $grupo->icone,
                        'cor' => $grupo->cor,
                        'descricao' => $grupo->descricao,
                        'ordem' => $grupo->ordem,
                    ];

                    // Adicionar stats se solicitado
                    if ($request->get('com_stats', false)) {
                        $data['total_templates'] = $grupo->total_templates ?? 0;
                        $data['templates_populares'] = $grupo->templates_populares ?? 0;
                    }

                    return $data;
                }),
                'message' => 'Grupos musculares listados com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar grupos musculares',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Exibe um grupo muscular específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $grupo = GrupoMuscular::ativo()
                ->withCount([
                    'exerciciosTemplates as total_templates' => function($q) {
                        $q->where('ativo', true);
                    },
                    'exerciciosTemplates as templates_populares' => function($q) {
                        $q->where('ativo', true)->where('popular', true);
                    },
                    'exercicios as total_exercicios_usuarios' => function($q) {
                        $q->where('status', 'ativo');
                    }
                ])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $grupo->id,
                    'nome' => $grupo->nome,
                    'slug' => $grupo->slug,
                    'icone' => $grupo->icone,
                    'cor' => $grupo->cor,
                    'descricao' => $grupo->descricao,
                    'ordem' => $grupo->ordem,
                    'stats' => [
                        'total_templates' => $grupo->total_templates,
                        'templates_populares' => $grupo->templates_populares,
                        'exercicios_usuarios' => $grupo->total_exercicios_usuarios,
                    ]
                ],
                'message' => 'Grupo muscular encontrado com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo muscular não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar grupo muscular',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista templates de exercícios de um grupo
     */
    public function exerciciosTemplates(Request $request, int $grupoId): JsonResponse
    {
        try {
            $grupo = GrupoMuscular::ativo()->findOrFail($grupoId);

            $query = $grupo->exerciciosTemplates()->ativo()->ordenado();

            // Filtros
            if ($request->get('apenas_populares', false)) {
                $query->popular();
            }

            if ($request->has('busca')) {
                $query->busca($request->busca);
            }

            $templates = $query->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'grupo' => [
                        'id' => $grupo->id,
                        'nome' => $grupo->nome,
                        'icone' => $grupo->icone,
                        'cor' => $grupo->cor,
                    ],
                    'templates' => $templates->map(function ($template) {
                        return [
                            'id' => $template->id,
                            'nome' => $template->nome,
                            'descricao' => $template->descricao,
                            'instrucoes' => $template->instrucoes,
                            'popular' => $template->popular,
                            'imagem_url' => $template->imagem_url,
                            'badge_popular' => $template->badge_popular,
                            'total_usos' => $template->total_usos,
                        ];
                    })
                ],
                'message' => "Templates do grupo {$grupo->nome} listados com sucesso"
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Grupo muscular não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar templates',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Cria um novo grupo muscular personalizado
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'nome' => 'required|string|max:100|unique:grupos_musculares,nome',
                'icone' => 'required|string|max:10',
                'cor' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'descricao' => 'nullable|string|max:500',
            ], [
                'nome.required' => 'O nome do grupo muscular é obrigatório.',
                'nome.unique' => 'Já existe um grupo muscular com este nome.',
                'icone.required' => 'O ícone é obrigatório.',
                'cor.required' => 'A cor é obrigatória.',
                'cor.regex' => 'A cor deve estar no formato hexadecimal (#RRGGBB).',
            ]);

            // Definir ordem automaticamente
            $ultimaOrdem = GrupoMuscular::max('ordem') ?? 0;
            $validatedData['ordem'] = $ultimaOrdem + 1;

            $grupo = GrupoMuscular::create($validatedData);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $grupo->id,
                    'nome' => $grupo->nome,
                    'slug' => $grupo->slug,
                    'icone' => $grupo->icone,
                    'cor' => $grupo->cor,
                    'descricao' => $grupo->descricao,
                    'ordem' => $grupo->ordem,
                ],
                'message' => 'Grupo muscular personalizado criado com sucesso'
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
                'message' => 'Erro ao criar grupo muscular',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista grupos mais utilizados
     */
    public function maisUtilizados(): JsonResponse
    {
        try {
            $grupos = GrupoMuscular::ativo()
                ->withCount([
                    'exercicios as total_usos' => function($q) {
                        $q->where('status', 'ativo');
                    }
                ])
                ->having('total_usos', '>', 0)
                ->orderBy('total_usos', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $grupos->map(function ($grupo) {
                    return [
                        'id' => $grupo->id,
                        'nome' => $grupo->nome,
                        'icone' => $grupo->icone,
                        'cor' => $grupo->cor,
                        'total_usos' => $grupo->total_usos,
                    ];
                }),
                'message' => 'Grupos mais utilizados listados com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar grupos mais utilizados',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}