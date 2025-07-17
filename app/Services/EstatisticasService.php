<?php

namespace App\Services;

use App\Models\ExecucaoTreino;
use App\Models\ExecucaoExercicio;
use App\Models\Treino;
use App\Models\Exercicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EstatisticasService
{
    /**
     * Obter estatísticas gerais do usuário
     */
    public function obterEstatisticasGerais(int $userId): array
    {
        $totalTreinosExecutados = $this->contarTreinosExecutados($userId);
        $totalExerciciosRealizados = $this->contarExerciciosRealizados($userId);
        $tempoTotalSegundos = $this->calcularTempoTotal($userId);
        $mediaDuracaoSegundos = $this->calcularMediaDuracao($userId);
        
        return [
            'total_treinos_executados' => $totalTreinosExecutados,
            'total_exercicios_realizados' => $totalExerciciosRealizados,
            'tempo_total_formatado' => $this->formatarTempo($tempoTotalSegundos),
            'tempo_total_segundos' => $tempoTotalSegundos,
            'media_duracao_formatada' => $this->formatarTempo($mediaDuracaoSegundos),
            'media_duracao_segundos' => round($mediaDuracaoSegundos),
        ];
    }

    /**
     * Obter estatísticas dos últimos N dias
     */
    public function obterEstatisticasPeriodo(int $userId, int $dias = 30): array
    {
        $dataInicio = Carbon::now()->subDays($dias);
        
        $treinosUltimos = ExecucaoTreino::where('user_id', $userId)
            ->where('status', 'finalizado')
            ->where('created_at', '>=', $dataInicio)
            ->count();
            
        $tempoUltimos = ExecucaoTreino::where('user_id', $userId)
            ->where('status', 'finalizado')
            ->where('created_at', '>=', $dataInicio)
            ->sum('tempo_total');

        $exerciciosUltimos = ExecucaoExercicio::whereHas('execucaoTreino', function($query) use ($userId, $dataInicio) {
            $query->where('user_id', $userId)
                  ->where('created_at', '>=', $dataInicio);
        })->where('concluido', true)->count();

        return [
            'periodo_dias' => $dias,
            'data_inicio' => $dataInicio->format('Y-m-d'),
            'treinos_realizados' => $treinosUltimos,
            'exercicios_realizados' => $exerciciosUltimos,
            'tempo_total_formatado' => $this->formatarTempo($tempoUltimos),
            'tempo_total_segundos' => $tempoUltimos,
            'media_por_dia' => round($treinosUltimos / $dias, 1),
            'media_tempo_por_treino' => $treinosUltimos > 0 ? $this->formatarTempo($tempoUltimos / $treinosUltimos) : '00:00:00',
        ];
    }

    /**
     * Obter favoritos (exercício e treino mais executados)
     */
    public function obterFavoritos(int $userId): array
    {
        // Exercício mais executado
        $exercicioMaisExecutado = ExecucaoExercicio::select('exercicio_id', DB::raw('COUNT(*) as total'))
            ->whereHas('execucaoTreino', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('concluido', true)
            ->groupBy('exercicio_id')
            ->orderBy('total', 'desc')
            ->with('exercicio:id,nome_exercicio,grupo_muscular')
            ->first();

        // Treino mais executado
        $treinoMaisExecutado = ExecucaoTreino::select('treino_id', DB::raw('COUNT(*) as total'))
            ->where('user_id', $userId)
            ->where('status', 'finalizado')
            ->groupBy('treino_id')
            ->orderBy('total', 'desc')
            ->with('treino:id,nome_treino,tipo_treino')
            ->first();

        return [
            'exercicio_mais_executado' => $exercicioMaisExecutado ? [
                'id' => $exercicioMaisExecutado->exercicio_id,
                'nome' => $exercicioMaisExecutado->exercicio->nome_exercicio ?? 'N/A',
                'grupo_muscular' => $exercicioMaisExecutado->exercicio->grupo_muscular ?? 'N/A',
                'total_execucoes' => $exercicioMaisExecutado->total,
            ] : null,
            'treino_mais_executado' => $treinoMaisExecutado ? [
                'id' => $treinoMaisExecutado->treino_id,
                'nome' => $treinoMaisExecutado->treino->nome_treino ?? 'N/A',
                'tipo' => $treinoMaisExecutado->treino->tipo_treino ?? 'N/A',
                'total_execucoes' => $treinoMaisExecutado->total,
            ] : null,
        ];
    }

    /**
     * Obter progresso diário por período
     */
    public function obterProgressoDiario(int $userId, int $dias = 30): Collection
    {
        $dataInicio = Carbon::now()->subDays($dias);

        return ExecucaoTreino::where('user_id', $userId)
            ->where('status', 'finalizado')
            ->where('created_at', '>=', $dataInicio)
            ->select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as total_treinos'),
                DB::raw('SUM(tempo_total) as tempo_total'),
                DB::raw('AVG(tempo_total) as tempo_medio')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('data')
            ->get()
            ->map(function($item) {
                return [
                    'data' => Carbon::parse($item->data)->format('Y-m-d'),
                    'data_formatada' => Carbon::parse($item->data)->format('d/m'),
                    'dia_semana' => Carbon::parse($item->data)->locale('pt_BR')->dayName,
                    'total_treinos' => $item->total_treinos,
                    'tempo_total' => $item->tempo_total,
                    'tempo_formatado' => $this->formatarTempo($item->tempo_total),
                    'tempo_medio' => round($item->tempo_medio),
                    'tempo_medio_formatado' => $this->formatarTempo($item->tempo_medio),
                ];
            });
    }

    /**
     * Obter top exercícios mais executados
     */
    public function obterTopExercicios(int $userId, int $limite = 10): Collection
    {
        return ExecucaoExercicio::select('exercicio_id', DB::raw('COUNT(*) as total_execucoes'))
            ->whereHas('execucaoTreino', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('concluido', true)
            ->groupBy('exercicio_id')
            ->orderBy('total_execucoes', 'desc')
            ->limit($limite)
            ->with('exercicio:id,nome_exercicio,grupo_muscular,tipo_execucao')
            ->get()
            ->map(function($item, $index) {
                return [
                    'posicao' => $index + 1,
                    'exercicio_id' => $item->exercicio_id,
                    'exercicio' => $item->exercicio->nome_exercicio ?? 'N/A',
                    'grupo_muscular' => $item->exercicio->grupo_muscular ?? 'N/A',
                    'tipo_execucao' => $item->exercicio->tipo_execucao ?? 'N/A',
                    'total_execucoes' => $item->total_execucoes,
                ];
            });
    }

    /**
     * Obter top treinos mais executados
     */
    public function obterTopTreinos(int $userId, int $limite = 10): Collection
    {
        return ExecucaoTreino::select(
                'treino_id', 
                DB::raw('COUNT(*) as total_execucoes'), 
                DB::raw('AVG(tempo_total) as tempo_medio'),
                DB::raw('SUM(tempo_total) as tempo_total_acumulado')
            )
            ->where('user_id', $userId)
            ->where('status', 'finalizado')
            ->groupBy('treino_id')
            ->orderBy('total_execucoes', 'desc')
            ->limit($limite)
            ->with('treino:id,nome_treino,tipo_treino,dificuldade')
            ->get()
            ->map(function($item, $index) {
                return [
                    'posicao' => $index + 1,
                    'treino_id' => $item->treino_id,
                    'treino' => $item->treino->nome_treino ?? 'N/A',
                    'tipo' => $item->treino->tipo_treino ?? 'N/A',
                    'dificuldade' => $item->treino->dificuldade ?? 'N/A',
                    'total_execucoes' => $item->total_execucoes,
                    'tempo_medio_formatado' => $this->formatarTempo($item->tempo_medio),
                    'tempo_total_formatado' => $this->formatarTempo($item->tempo_total_acumulado),
                ];
            });
    }

    /**
     * Obter recordes de peso por exercício
     */
    public function obterRecordesPeso(int $userId, int $limite = 10): Collection
    {
        return ExecucaoExercicio::select(
                'exercicio_id', 
                DB::raw('MAX(peso_utilizado) as maior_peso'),
                DB::raw('MAX(repeticoes_realizadas) as maior_repeticoes'),
                DB::raw('COUNT(*) as total_execucoes')
            )
            ->whereHas('execucaoTreino', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('concluido', true)
            ->whereNotNull('peso_utilizado')
            ->where('peso_utilizado', '>', 0)
            ->groupBy('exercicio_id')
            ->orderBy('maior_peso', 'desc')
            ->limit($limite)
            ->with('exercicio:id,nome_exercicio,grupo_muscular')
            ->get()
            ->map(function($item, $index) {
                return [
                    'posicao' => $index + 1,
                    'exercicio_id' => $item->exercicio_id,
                    'exercicio' => $item->exercicio->nome_exercicio ?? 'N/A',
                    'grupo_muscular' => $item->exercicio->grupo_muscular ?? 'N/A',
                    'maior_peso' => $item->maior_peso,
                    'maior_repeticoes' => $item->maior_repeticoes,
                    'total_execucoes' => $item->total_execucoes,
                ];
            });
    }

    /**
     * Obter estatísticas por grupo muscular
     */
    public function obterEstatisticasGrupoMuscular(int $userId): Collection
    {
        return ExecucaoExercicio::select(
                'exercicios.grupo_muscular',
                DB::raw('COUNT(*) as total_exercicios'),
                DB::raw('COUNT(DISTINCT exercicio_id) as exercicios_diferentes'),
                DB::raw('SUM(execucao_exercicios.series_realizadas) as total_series'),
                DB::raw('SUM(execucao_exercicios.repeticoes_realizadas) as total_repeticoes'),
                DB::raw('AVG(execucao_exercicios.peso_utilizado) as peso_medio'),
                DB::raw('MAX(execucao_exercicios.peso_utilizado) as maior_peso'),
                DB::raw('SUM(execucao_exercicios.tempo_execucao) as tempo_total')
            )
            ->join('exercicios', 'execucao_exercicios.exercicio_id', '=', 'exercicios.id')
            ->whereHas('execucaoTreino', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('execucao_exercicios.concluido', true)
            ->whereNotNull('exercicios.grupo_muscular')
            ->groupBy('exercicios.grupo_muscular')
            ->orderBy('total_exercicios', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'grupo_muscular' => $item->grupo_muscular,
                    'total_exercicios' => $item->total_exercicios,
                    'exercicios_diferentes' => $item->exercicios_diferentes,
                    'total_series' => $item->total_series ?? 0,
                    'total_repeticoes' => $item->total_repeticoes ?? 0,
                    'peso_medio' => $item->peso_medio ? round($item->peso_medio, 1) : 0,
                    'maior_peso' => $item->maior_peso ?? 0,
                    'tempo_total' => $item->tempo_total ?? 0,
                    'tempo_total_formatado' => $this->formatarTempo($item->tempo_total ?? 0),
                ];
            });
    }

    /**
     * Obter evolução completa de um exercício
     */
    public function obterEvolucaoExercicio(int $userId, int $exercicioId): array
    {
        // Buscar todas as execuções deste exercício
        $execucoes = ExecucaoExercicio::where('exercicio_id', $exercicioId)
            ->whereHas('execucaoTreino', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('concluido', true)
            ->orderBy('created_at')
            ->get();

        // Calcular estatísticas
        $totalExecucoes = $execucoes->count();
        $maiorPeso = $execucoes->max('peso_utilizado') ?? 0;
        $maiorRepeticoes = $execucoes->max('repeticoes_realizadas') ?? 0;
        $pesoMedio = $execucoes->avg('peso_utilizado') ?? 0;
        $repeticoesMedio = $execucoes->avg('repeticoes_realizadas') ?? 0;
        
        // Formatar histórico
        $historico = $execucoes->map(function($item) {
            return [
                'data' => $item->created_at->format('Y-m-d'),
                'data_formatada' => $item->created_at->format('d/m/Y'),
                'series_realizadas' => $item->series_realizadas,
                'repeticoes_realizadas' => $item->repeticoes_realizadas,
                'peso_utilizado' => $item->peso_utilizado,
                'tempo_execucao' => $item->tempo_execucao,
                'tempo_execucao_formatado' => $this->formatarTempo($item->tempo_execucao),
                'observacoes' => $item->observacoes,
            ];
        });

        // Calcular progressão (últimas 5 execuções vs primeiras 5)
        $progressao = $this->calcularProgressaoExercicio($execucoes);

        return [
            'estatisticas' => [
                'total_execucoes' => $totalExecucoes,
                'maior_peso' => $maiorPeso,
                'maior_repeticoes' => $maiorRepeticoes,
                'peso_medio' => round($pesoMedio, 1),
                'repeticoes_medio' => round($repeticoesMedio, 1),
                'primeira_execucao' => $execucoes->first()?->created_at?->format('d/m/Y'),
                'ultima_execucao' => $execucoes->last()?->created_at?->format('d/m/Y'),
            ],
            'progressao' => $progressao,
            'historico' => $historico,
        ];
    }

    /**
     * Calcular sequências de treinos
     */
    public function calcularSequencias(int $userId): array
    {
        return [
            'sequencia_atual' => $this->calcularSequenciaAtual($userId),
            'maior_sequencia' => $this->calcularMaiorSequencia($userId),
            'sequencia_semanal' => $this->calcularSequenciaSemanal($userId),
        ];
    }

    /**
     * Obter métricas de consistência
     */
    public function obterMetricasConsistencia(int $userId): array
    {
        $hoje = Carbon::now();
        
        // Últimos 7 dias
        $treinos7Dias = $this->contarTreinosPeriodo($userId, 7);
        
        // Últimas 4 semanas
        $treinos4Semanas = $this->contarTreinosPeriodo($userId, 28);
        
        // Últimos 3 meses
        $treinos3Meses = $this->contarTreinosPeriodo($userId, 90);
        
        return [
            'ultimos_7_dias' => [
                'treinos' => $treinos7Dias,
                'meta_diaria' => 1,
                'percentual_meta' => round(($treinos7Dias / 7) * 100, 1),
            ],
            'ultimas_4_semanas' => [
                'treinos' => $treinos4Semanas,
                'meta_semanal' => 3,
                'percentual_meta' => round(($treinos4Semanas / (4 * 3)) * 100, 1),
            ],
            'ultimos_3_meses' => [
                'treinos' => $treinos3Meses,
                'meta_mensal' => 12,
                'percentual_meta' => round(($treinos3Meses / (3 * 12)) * 100, 1),
            ],
        ];
    }

    // ========== MÉTODOS PRIVADOS DE CÁLCULO ==========

    private function contarTreinosExecutados(int $userId): int
    {
        return ExecucaoTreino::where('user_id', $userId)
            ->whereIn('status', ['finalizado', 'pausado'])
            ->count();
    }

    private function contarExerciciosRealizados(int $userId): int
    {
        return ExecucaoExercicio::whereHas('execucaoTreino', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('concluido', true)->count();
    }

    private function calcularTempoTotal(int $userId): int
    {
        return ExecucaoTreino::where('user_id', $userId)
            ->where('status', 'finalizado')
            ->sum('tempo_total') ?? 0;
    }

    private function calcularMediaDuracao(int $userId): float
    {
        return ExecucaoTreino::where('user_id', $userId)
            ->where('status', 'finalizado')
            ->avg('tempo_total') ?? 0;
    }

    private function contarTreinosPeriodo(int $userId, int $dias): int
    {
        $dataInicio = Carbon::now()->subDays($dias);
        
        return ExecucaoTreino::where('user_id', $userId)
            ->where('status', 'finalizado')
            ->where('created_at', '>=', $dataInicio)
            ->count();
    }

    private function calcularSequenciaAtual(int $userId): int
    {
        $hoje = Carbon::today();
        $sequencia = 0;
        
        for ($i = 0; $i < 365; $i++) {
            $data = $hoje->copy()->subDays($i);
            
            $treinoNoDia = ExecucaoTreino::where('user_id', $userId)
                ->where('status', 'finalizado')
                ->whereDate('created_at', $data)
                ->exists();
                
            if ($treinoNoDia) {
                $sequencia++;
            } else {
                break;
            }
        }
        
        return $sequencia;
    }

    private function calcularMaiorSequencia(int $userId): int
    {
        $treinos = ExecucaoTreino::where('user_id', $userId)
            ->where('status', 'finalizado')
            ->select(DB::raw('DATE(created_at) as data'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('data')
            ->pluck('data')
            ->toArray();
            
        if (empty($treinos)) return 0;
        
        $maiorSequencia = 1;
        $sequenciaAtual = 1;
        
        for ($i = 1; $i < count($treinos); $i++) {
            $dataAnterior = Carbon::parse($treinos[$i-1]);
            $dataAtual = Carbon::parse($treinos[$i]);
            
            if ($dataAtual->diffInDays($dataAnterior) === 1) {
                $sequenciaAtual++;
                $maiorSequencia = max($maiorSequencia, $sequenciaAtual);
            } else {
                $sequenciaAtual = 1;
            }
        }
        
        return $maiorSequencia;
    }

    private function calcularSequenciaSemanal(int $userId): int
    {
        $semanasConsecutivas = 0;
        $semanaAtual = Carbon::now()->startOfWeek();
        
        for ($i = 0; $i < 52; $i++) {
            $inicioSemana = $semanaAtual->copy()->subWeeks($i);
            $fimSemana = $inicioSemana->copy()->endOfWeek();
            
            $treinosNaSemana = ExecucaoTreino::where('user_id', $userId)
                ->where('status', 'finalizado')
                ->whereBetween('created_at', [$inicioSemana, $fimSemana])
                ->count();
                
            if ($treinosNaSemana > 0) {
                $semanasConsecutivas++;
            } else {
                break;
            }
        }
        
        return $semanasConsecutivas;
    }

    private function calcularProgressaoExercicio(Collection $execucoes): array
    {
        if ($execucoes->count() < 2) {
            return [
                'peso' => 0,
                'repeticoes' => 0,
                'series' => 0,
                'tempo' => 0,
            ];
        }
        
        // Pegar primeiras e últimas 5 execuções para comparar
        $primeiras = $execucoes->take(5);
        $ultimas = $execucoes->reverse()->take(5);
        
        $pesoInicial = $primeiras->avg('peso_utilizado') ?? 0;
        $pesoAtual = $ultimas->avg('peso_utilizado') ?? 0;
        
        $repeticoesInicial = $primeiras->avg('repeticoes_realizadas') ?? 0;
        $repeticoesAtual = $ultimas->avg('repeticoes_realizadas') ?? 0;
        
        $seriesInicial = $primeiras->avg('series_realizadas') ?? 0;
        $seriesAtual = $ultimas->avg('series_realizadas') ?? 0;
        
        $tempoInicial = $primeiras->avg('tempo_execucao') ?? 0;
        $tempoAtual = $ultimas->avg('tempo_execucao') ?? 0;
        
        return [
            'peso' => round($pesoAtual - $pesoInicial, 1),
            'repeticoes' => round($repeticoesAtual - $repeticoesInicial, 1),
            'series' => round($seriesAtual - $seriesInicial, 1),
            'tempo' => round($tempoAtual - $tempoInicial),
        ];
    }

    public function formatarTempo(?int $segundos): string
    {
        if (!$segundos) return '00:00:00';
        
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segs = $segundos % 60;
        
        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segs);
    }
}