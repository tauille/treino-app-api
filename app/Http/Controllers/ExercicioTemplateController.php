<?php

namespace App\Http\Controllers;

use App\Models\ExercicioTemplate;
use App\Models\GrupoMuscular;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ExercicioTemplateController extends Controller
{
    /**
     * Lista todos os templates de exercícios
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ExercicioTemplate::ativo()->comGrupo()->ordenado();

            // Filtros
            if ($request->has('grupo_muscular_id')) {
                $query->doGrupo($request->grupo_muscular_id);
            }

            if ($request->get('apenas_populares', false)) {
                $query->popular();
            }

            if ($request->has('busca')) {
                $query->busca($request->busca);
            }

            // Paginação
            $perPage = $request->get('per_page', 20);
            $templates = $query->paginate($perPage);

            // Transformar dados
            $templates->getCollection()->transform(function ($template) {
                return [
                    'id' => $template->id,
                    'nome' => $template->nome,
                    'descricao' => $template->descricao,
                    'instrucoes' => $template->instrucoes,
                    'popular' => $template->popular,
                    'badge_popular' => $template->badge_popular,
                    'imagem_url' => $template->imagem_url,
                    'total_usos' => $template->total_usos,
                    'grupo_muscular' => [
                        'id' => $template->grupoMuscular->id,
                        'nome' => $template->grupoMuscular->nome,
                        'icone' => $template->grupoMuscular->icone,
                        'cor' => $template->grupoMuscular->cor,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $templates,
                'message' => 'Templates de exercícios listados com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar templates',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista apenas exercícios populares
     */
    public function populares(Request $request): JsonResponse
    {
        try {
            $query = ExercicioTemplate::ativo()->popular()->comGrupo()->ordenado();

            // Filtro por grupo se especificado
            if ($request->has('grupo_muscular_id')) {
                $query->doGrupo($request->grupo_muscular_id);
            }

            // Limit para não trazer muitos
            $limit = $request->get('limit', 10);
            $templates = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $templates->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'nome' => $template->nome,
                        'descricao' => $template->descricao,
                        'total_usos' => $template->total_usos,
                        'grupo_muscular' => [
                            'id' => $template->grupoMuscular->id,
                            'nome' => $template->grupoMuscular->nome,
                            'icone' => $template->grupoMuscular->icone,
                            'cor' => $template->grupoMuscular->cor,
                        ]
                    ];
                }),
                'message' => 'Templates populares listados com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar templates populares',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Exibe um template específico
     */
    public function show(int $id): JsonResponse
    {
        try {
            $template = ExercicioTemplate::ativo()
                ->comGrupo()
                ->withCount('exercicios as total_usos')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'nome' => $template->nome,
                    'descricao' => $template->descricao,
                    'instrucoes' => $template->instrucoes,
                    'popular' => $template->popular,
                    'badge_popular' => $template->badge_popular,
                    'imagem_url' => $template->imagem_url,
                    'total_usos' => $template->total_usos,
                    'grupo_muscular' => [
                        'id' => $template->grupoMuscular->id,
                        'nome' => $template->grupoMuscular->nome,
                        'slug' => $template->grupoMuscular->slug,
                        'icone' => $template->grupoMuscular->icone,
                        'cor' => $template->grupoMuscular->cor,
                        'descricao' => $template->grupoMuscular->descricao,
                    ],
                    'created_at' => $template->created_at,
                    'updated_at' => $template->updated_at,
                ],
                'message' => 'Template encontrado com sucesso'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template não encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar template',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Cria um novo template personalizado
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'nome' => 'required|string|max:255',
                'grupo_muscular_id' => 'required|exists:grupos_musculares,id',
                'descricao' => 'nullable|string|max:1000',
                'instrucoes' => 'nullable|string|max:2000',
                'imagem_url' => 'nullable|url|max:500',
                'popular' => 'nullable|boolean',
            ], [
                'nome.required' => 'O nome do exercício é obrigatório.',
                'grupo_muscular_id.required' => 'O grupo muscular é obrigatório.',
                'grupo_muscular_id.exists' => 'Grupo muscular inválido.',
                'imagem_url.url' => 'A URL da imagem deve ser válida.',
            ]);

            // Por padrão, templates criados por usuários não são populares
            $validatedData['popular'] = $validatedData['popular'] ?? false;

            $template = ExercicioTemplate::create($validatedData);
            $template->load('grupoMuscular:id,nome,icone,cor');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $template->id,
                    'nome' => $template->nome,
                    'descricao' => $template->descricao,
                    'instrucoes' => $template->instrucoes,
                    'popular' => $template->popular,
                    'grupo_muscular' => [
                        'id' => $template->grupoMuscular->id,
                        'nome' => $template->grupoMuscular->nome,
                        'icone' => $template->grupoMuscular->icone,
                        'cor' => $template->grupoMuscular->cor,
                    ]
                ],
                'message' => 'Template personalizado criado com sucesso'
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
                'message' => 'Erro ao criar template',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Busca templates por texto
     */
    public function buscar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2|max:100',
                'grupo_muscular_id' => 'nullable|exists:grupos_musculares,id',
                'apenas_populares' => 'nullable|boolean',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            $query = ExercicioTemplate::ativo()
                ->comGrupo()
                ->busca($request->q)
                ->ordenado();

            // Filtros opcionais
            if ($request->has('grupo_muscular_id')) {
                $query->doGrupo($request->grupo_muscular_id);
            }

            if ($request->get('apenas_populares', false)) {
                $query->popular();
            }

            $limit = $request->get('limit', 15);
            $templates = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'termo_busca' => $request->q,
                    'total_encontrados' => $templates->count(),
                    'templates' => $templates->map(function ($template) {
                        return [
                            'id' => $template->id,
                            'nome' => $template->nome,
                            'descricao' => $template->descricao,
                            'popular' => $template->popular,
                            'grupo_muscular' => [
                                'id' => $template->grupoMuscular->id,
                                'nome' => $template->grupoMuscular->nome,
                                'icone' => $template->grupoMuscular->icone,
                                'cor' => $template->grupoMuscular->cor,
                            ]
                        ];
                    })
                ],
                'message' => "Busca por '{$request->q}' realizada com sucesso"
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parâmetros de busca inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar busca',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista templates mais utilizados
     */
    public function maisUtilizados(Request $request): JsonResponse
    {
        try {
            $query = ExercicioTemplate::ativo()
                ->comGrupo()
                ->withCount('exercicios as total_usos')
                ->having('total_usos', '>', 0)
                ->orderBy('total_usos', 'desc');

            // Filtro por grupo se especificado
            if ($request->has('grupo_muscular_id')) {
                $query->doGrupo($request->grupo_muscular_id);
            }

            $limit = $request->get('limit', 10);
            $templates = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $templates->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'nome' => $template->nome,
                        'total_usos' => $template->total_usos,
                        'popular' => $template->popular,
                        'grupo_muscular' => [
                            'id' => $template->grupoMuscular->id,
                            'nome' => $template->grupoMuscular->nome,
                            'icone' => $template->grupoMuscular->icone,
                            'cor' => $template->grupoMuscular->cor,
                        ]
                    ];
                }),
                'message' => 'Templates mais utilizados listados com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar templates mais utilizados',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}