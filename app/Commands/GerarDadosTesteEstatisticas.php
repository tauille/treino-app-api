<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Treino;
use App\Models\Exercicio;
use App\Models\ExecucaoTreino;
use App\Models\ExecucaoExercicio;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Faker\Factory as Faker;

class GerarDadosTesteEstatisticas extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'estatisticas:gerar-dados-teste 
                            {--user= : ID do usuário para gerar dados (opcional)}
                            {--dias=90 : Número de dias para gerar dados}
                            {--treinos=3 : Treinos por semana em média}
                            {--exercicios=8 : Exercícios por treino em média}
                            {--limpar : Limpar dados existentes antes de gerar}
                            {--verbose : Mostrar detalhes do processo}';

    /**
     * The console command description.
     */
    protected $description = 'Gera dados de teste para estatísticas de treinos e exercícios';

    private $faker;
    private $grupos_musculares = [
        'Peito', 'Costas', 'Ombros', 'Bíceps', 'Tríceps', 
        'Pernas', 'Glúteos', 'Abdômen', 'Cardio'
    ];

    private $exercicios_por_grupo = [
        'Peito' => ['Supino Reto', 'Supino Inclinado', 'Crucifixo', 'Flexão', 'Supino Declinado'],
        'Costas' => ['Puxada', 'Remada', 'Barra Fixa', 'Remada Curvada', 'Pullover'],
        'Ombros' => ['Desenvolvimento', 'Elevação Lateral', 'Elevação Frontal', 'Encolhimento'],
        'Bíceps' => ['Rosca Direta', 'Rosca Martelo', 'Rosca Concentrada', 'Rosca 21'],
        'Tríceps' => ['Tríceps Testa', 'Tríceps Pulley', 'Mergulho', 'Tríceps Banco'],
        'Pernas' => ['Agachamento', 'Leg Press', 'Mesa Flexora', 'Cadeira Extensora'],
        'Glúteos' => ['Hip Thrust', 'Agachamento Búlgaro', 'Stiff', 'Glúteo 4 Apoios'],
        'Abdômen' => ['Abdominal', 'Prancha', 'Bicicleta', 'Crunch'],
        'Cardio' => ['Esteira', 'Bike', 'Elíptico', 'Remada Cardio'],
    ];

    private $tipos_treino = [
        'Musculação', 'Cardio', 'Funcional', 'Crossfit', 
        'Pilates', 'Yoga', 'Calistenia', 'HIIT'
    ];

    private $dificuldades = ['iniciante', 'intermediario', 'avancado'];

    public function handle()
    {
        $this->faker = Faker::create('pt_BR');
        
        $this->info('🏋️ Iniciando geração de dados de teste para estatísticas...');
        
        // Obter parâmetros
        $userId = $this->option('user');
        $dias = (int) $this->option('dias');
        $treinosPorSemana = (int) $this->option('treinos');
        $exerciciosPorTreino = (int) $this->option('exercicios');
        $limpar = $this->option('limpar');
        $verbose = $this->option('verbose');

        // Validar usuário
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("❌ Usuário com ID {$userId} não encontrado!");
                return 1;
            }
        } else {
            // Buscar primeiro usuário ou criar um
            $user = User::first();
            if (!$user) {
                $user = User::factory()->create([
                    'name' => 'Usuário Teste',
                    'email' => 'teste@treino.app',
                ]);
                $this->info("✅ Usuário de teste criado: {$user->email}");
            }
        }

        $this->info("👤 Gerando dados para: {$user->name} (ID: {$user->id})");

        // Limpar dados existentes se solicitado
        if ($limpar) {
            $this->limparDadosExistentes($user->id, $verbose);
        }

        // Gerar dados
        $this->gerarTreinosExerciciosBase($user->id, $verbose);
        $this->gerarExecucoes($user->id, $dias, $treinosPorSemana, $exerciciosPorTreino, $verbose);

        $this->info('✅ Dados de teste gerados com sucesso!');
        $this->mostrarResumo($user->id);

        return 0;
    }

    private function limparDadosExistentes(int $userId, bool $verbose): void
    {
        if ($verbose) $this->info('🧹 Limpando dados existentes...');

        // Deletar execuções de exercícios
        ExecucaoExercicio::whereHas('execucaoTreino', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->delete();

        // Deletar execuções de treinos
        ExecucaoTreino::where('user_id', $userId)->delete();

        // Deletar exercícios dos treinos do usuário
        Exercicio::whereHas('treino', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->delete();

        // Deletar treinos
        Treino::where('user_id', $userId)->delete();

        if ($verbose) $this->info('✅ Dados existentes removidos');
    }

    private function gerarTreinosExerciciosBase(int $userId, bool $verbose): void
    {
        if ($verbose) $this->info('🏋️ Criando treinos base...');

        $treinos = [];
        
        // Criar diferentes tipos de treino
        foreach (['Push', 'Pull', 'Legs', 'Upper', 'Lower', 'Full Body'] as $tipo) {
            $treino = Treino::create([
                'user_id' => $userId,
                'nome_treino' => "Treino {$tipo}",
                'tipo_treino' => $this->faker->randomElement($this->tipos_treino),
                'descricao' => "Treino focado em {$tipo}",
                'dificuldade' => $this->faker->randomElement($this->dificuldades),
                'status' => 'ativo',
            ]);

            $treinos[] = $treino;

            // Criar exercícios para cada treino
            $this->criarExerciciosParaTreino($treino, $tipo, $verbose);
        }

        if ($verbose) $this->info("✅ {count($treinos)} treinos criados");
    }

    private function criarExerciciosParaTreino(Treino $treino, string $tipo, bool $verbose): void
    {
        $gruposParaTipo = $this->obterGruposMuscularesPorTipo($tipo);
        
        $ordem = 1;
        foreach ($gruposParaTipo as $grupo) {
            $exerciciosDoGrupo = $this->exercicios_por_grupo[$grupo] ?? ['Exercício Genérico'];
            
            // 1-3 exercícios por grupo muscular
            $quantidadeExercicios = $this->faker->numberBetween(1, 3);
            $exerciciosEscolhidos = $this->faker->randomElements($exerciciosDoGrupo, $quantidadeExercicios);
            
            foreach ($exerciciosEscolhidos as $nomeExercicio) {
                $tipoExecucao = $this->faker->randomElement(['repeticao', 'tempo']);
                
                Exercicio::create([
                    'treino_id' => $treino->id,
                    'nome_exercicio' => $nomeExercicio,
                    'descricao' => "Exercício de {$grupo}",
                    'grupo_muscular' => $grupo,
                    'tipo_execucao' => $tipoExecucao,
                    'repeticoes' => $tipoExecucao === 'repeticao' ? $this->faker->numberBetween(8, 15) : null,
                    'series' => $this->faker->numberBetween(3, 5),
                    'tempo_execucao' => $tipoExecucao === 'tempo' ? $this->faker->numberBetween(30, 120) : null,
                    'tempo_descanso' => $this->faker->numberBetween(60, 180),
                    'peso' => $this->faker->randomFloat(1, 10, 100),
                    'unidade_peso' => 'kg',
                    'ordem' => $ordem++,
                    'status' => 'ativo',
                ]);
            }
        }

        if ($verbose) $this->info("  ✅ Exercícios criados para {$treino->nome_treino}");
    }

    private function obterGruposMuscularesPorTipo(string $tipo): array
    {
        return match($tipo) {
            'Push' => ['Peito', 'Ombros', 'Tríceps'],
            'Pull' => ['Costas', 'Bíceps'],
            'Legs' => ['Pernas', 'Glúteos'],
            'Upper' => ['Peito', 'Costas', 'Ombros', 'Bíceps', 'Tríceps'],
            'Lower' => ['Pernas', 'Glúteos', 'Abdômen'],
            'Full Body' => ['Peito', 'Costas', 'Pernas', 'Ombros'],
            default => $this->faker->randomElements($this->grupos_musculares, 3),
        };
    }

    private function gerarExecucoes(int $userId, int $dias, int $treinosPorSemana, int $exerciciosPorTreino, bool $verbose): void
    {
        if ($verbose) $this->info("📊 Gerando execuções para {$dias} dias...");

        $treinos = Treino::where('user_id', $userId)->with('exercicios')->get();
        $dataInicio = Carbon::now()->subDays($dias);
        
        $totalExecucoes = 0;
        $totalExerciciosExecutados = 0;

        // Gerar execuções ao longo do período
        for ($i = 0; $i < $dias; $i++) {
            $data = $dataInicio->copy()->addDays($i);
            
            // Chance de treinar baseada na meta semanal
            $chanceDeTreeinar = ($treinosPorSemana / 7) * 100;
            
            // Adicionar variação nos fins de semana
            if ($data->isWeekend()) {
                $chanceDeTreeinar *= 0.7; // 30% menos chance no fim de semana
            }
            
            if ($this->faker->numberBetween(1, 100) <= $chanceDeTreeinar) {
                $treino = $this->faker->randomElement($treinos);
                
                $execucao = $this->criarExecucaoTreino($treino, $data, $exerciciosPorTreino);
                $exerciciosExecutados = $this->criarExecucoesExercicios($execucao, $treino);
                
                $totalExecucoes++;
                $totalExerciciosExecutados += $exerciciosExecutados;
                
                if ($verbose && $totalExecucoes % 10 === 0) {
                    $this->info("  📈 {$totalExecucoes} execuções criadas...");
                }
            }
        }

        if ($verbose) {
            $this->info("✅ Total: {$totalExecucoes} treinos, {$totalExerciciosExecutados} exercícios");
        }
    }

    private function criarExecucaoTreino(Treino $treino, Carbon $data, int $exerciciosPorTreino): ExecucaoTreino
    {
        $tempoTotal = $this->faker->numberBetween(1800, 7200); // 30min a 2h
        
        return ExecucaoTreino::create([
            'user_id' => $treino->user_id,
            'treino_id' => $treino->id,
            'status' => 'finalizado',
            'tempo_total' => $tempoTotal,
            'observacoes' => $this->faker->optional(0.3)->sentence(),
            'created_at' => $data->addMinutes($this->faker->numberBetween(0, 1439)),
            'updated_at' => $data,
        ]);
    }

    private function criarExecucoesExercicios(ExecucaoTreino $execucao, Treino $treino): int
    {
        $exercicios = $treino->exercicios->shuffle();
        $quantidadeExercicios = min($exercicios->count(), $this->faker->numberBetween(5, 12));
        $exerciciosEscolhidos = $exercicios->take($quantidadeExercicios);
        
        foreach ($exerciciosEscolhidos as $exercicio) {
            // 90% de chance de concluir o exercício
            $concluido = $this->faker->boolean(90);
            
            if ($concluido) {
                $this->criarExecucaoExercicio($execucao, $exercicio);
            }
        }
        
        return $exerciciosEscolhidos->count();
    }

    private function criarExecucaoExercicio(ExecucaoTreino $execucao, Exercicio $exercicio): void
    {
        $pesoBase = $exercicio->peso ?? 0;
        $pesoVariacao = $this->faker->numberBetween(-20, 20) / 100; // ±20%
        $pesoUtilizado = max(0, $pesoBase * (1 + $pesoVariacao));
        
        $seriesBase = $exercicio->series ?? 3;
        $seriesRealizadas = max(1, $seriesBase + $this->faker->numberBetween(-1, 1));
        
        $repeticoesBase = $exercicio->repeticoes ?? 10;
        $repeticoesRealizadas = max(1, $repeticoesBase + $this->faker->numberBetween(-3, 3));
        
        $tempoExecucao = $exercicio->tempo_execucao ?? $this->faker->numberBetween(60, 300);
        
        ExecucaoExercicio::create([
            'execucao_treino_id' => $execucao->id,
            'exercicio_id' => $exercicio->id,
            'concluido' => true,
            'series_realizadas' => $seriesRealizadas,
            'repeticoes_realizadas' => $repeticoesRealizadas,
            'peso_utilizado' => round($pesoUtilizado, 1),
            'tempo_execucao' => $tempoExecucao,
            'observacoes' => $this->faker->optional(0.2)->sentence(),
            'created_at' => $execucao->created_at,
            'updated_at' => $execucao->updated_at,
        ]);
    }

    private function mostrarResumo(int $userId): void
    {
        $totalTreinos = ExecucaoTreino::where('user_id', $userId)->count();
        $totalExercicios = ExecucaoExercicio::whereHas('execucaoTreino', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->count();
        
        $tempoTotal = ExecucaoTreino::where('user_id', $userId)->sum('tempo_total');
        $tempoFormatado = gmdate('H:i:s', $tempoTotal);
        
        $this->info('');
        $this->info('📊 RESUMO DOS DADOS GERADOS:');
        $this->info("   🏋️ Treinos executados: {$totalTreinos}");
        $this->info("   💪 Exercícios realizados: {$totalExercicios}");
        $this->info("   ⏱️ Tempo total: {$tempoFormatado}");
        $this->info('');
        $this->info('🎯 Para testar as estatísticas, acesse:');
        $this->info('   GET /api/estatisticas/dashboard');
        $this->info('   GET /api/estatisticas/progresso');
        $this->info('   GET /api/estatisticas/rankings');
    }
}