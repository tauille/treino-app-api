<?php

namespace Database\Seeders;

use App\Models\ExercicioTemplate;
use App\Models\GrupoMuscular;
use Illuminate\Database\Seeder;

class ExercicioTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar grupos musculares por slug
        $grupos = GrupoMuscular::whereIn('slug', [
            'peito', 'costas', 'pernas', 'ombros', 'bracos', 'core', 'cardio', 'funcional'
        ])->get()->keyBy('slug');

        $exercicios = [
            // 💪 PEITO
            'peito' => [
                ['nome' => 'Supino Reto', 'popular' => true, 'descricao' => 'Exercício básico para desenvolvimento do peitoral maior'],
                ['nome' => 'Supino Inclinado', 'popular' => true, 'descricao' => 'Foco na porção superior do peitoral'],
                ['nome' => 'Supino Declinado', 'popular' => false, 'descricao' => 'Foco na porção inferior do peitoral'],
                ['nome' => 'Flexão de Braço', 'popular' => true, 'descricao' => 'Exercício com peso corporal para peito'],
                ['nome' => 'Crucifixo', 'popular' => true, 'descricao' => 'Movimento de abertura para isolamento do peitoral'],
                ['nome' => 'Cross Over', 'popular' => false, 'descricao' => 'Exercício no cabo para definição'],
                ['nome' => 'Pullover', 'popular' => false, 'descricao' => 'Exercício para expansão da caixa torácica'],
            ],

            // 🏋️ COSTAS
            'costas' => [
                ['nome' => 'Puxada Frontal', 'popular' => true, 'descricao' => 'Exercício fundamental para latíssimo do dorso'],
                ['nome' => 'Puxada Posterior', 'popular' => false, 'descricao' => 'Variação com pegada invertida'],
                ['nome' => 'Remada Curvada', 'popular' => true, 'descricao' => 'Exercício composto para todas as áreas das costas'],
                ['nome' => 'Remada Sentada', 'popular' => true, 'descricao' => 'Exercício no cabo para densidade das costas'],
                ['nome' => 'Barra Fixa', 'popular' => true, 'descricao' => 'Exercício com peso corporal para costas'],
                ['nome' => 'Levantamento Terra', 'popular' => true, 'descricao' => 'Exercício composto fundamental'],
                ['nome' => 'Remada Unilateral', 'popular' => false, 'descricao' => 'Trabalho unilateral com halteres'],
            ],

            // 🦵 PERNAS
            'pernas' => [
                ['nome' => 'Agachamento', 'popular' => true, 'descricao' => 'Exercício fundamental para pernas e glúteos'],
                ['nome' => 'Leg Press', 'popular' => true, 'descricao' => 'Exercício na máquina para quadríceps'],
                ['nome' => 'Stiff', 'popular' => true, 'descricao' => 'Exercício para isquiotibiais e glúteos'],
                ['nome' => 'Afundo', 'popular' => true, 'descricao' => 'Exercício unilateral para pernas'],
                ['nome' => 'Extensão de Quadríceps', 'popular' => false, 'descricao' => 'Isolamento do quadríceps'],
                ['nome' => 'Mesa Flexora', 'popular' => false, 'descricao' => 'Isolamento dos isquiotibiais'],
                ['nome' => 'Panturrilha em Pé', 'popular' => true, 'descricao' => 'Exercício para panturrilhas'],
                ['nome' => 'Panturrilha Sentada', 'popular' => false, 'descricao' => 'Variação sentada para panturrilhas'],
            ],

            // 💪 OMBROS
            'ombros' => [
                ['nome' => 'Desenvolvimento', 'popular' => true, 'descricao' => 'Exercício básico para deltoides'],
                ['nome' => 'Elevação Lateral', 'popular' => true, 'descricao' => 'Isolamento do deltoide lateral'],
                ['nome' => 'Elevação Posterior', 'popular' => true, 'descricao' => 'Foco no deltoide posterior'],
                ['nome' => 'Elevação Frontal', 'popular' => false, 'descricao' => 'Isolamento do deltoide anterior'],
                ['nome' => 'Desenvolvimento Arnold', 'popular' => false, 'descricao' => 'Variação do desenvolvimento'],
                ['nome' => 'Upright Row', 'popular' => false, 'descricao' => 'Remada alta para ombros'],
            ],

            // 💪 BRAÇOS
            'bracos' => [
                ['nome' => 'Rosca Bíceps', 'popular' => true, 'descricao' => 'Exercício básico para bíceps'],
                ['nome' => 'Rosca Martelo', 'popular' => true, 'descricao' => 'Variação para bíceps e antebraços'],
                ['nome' => 'Tríceps Testa', 'popular' => true, 'descricao' => 'Exercício para tríceps'],
                ['nome' => 'Tríceps Francês', 'popular' => true, 'descricao' => 'Variação para tríceps'],
                ['nome' => 'Mergulho', 'popular' => true, 'descricao' => 'Exercício com peso corporal'],
                ['nome' => 'Rosca Concentrada', 'popular' => false, 'descricao' => 'Isolamento máximo do bíceps'],
                ['nome' => 'Tríceps Corda', 'popular' => false, 'descricao' => 'Exercício no cabo'],
            ],

            // 🎯 CORE
            'core' => [
                ['nome' => 'Abdominal', 'popular' => true, 'descricao' => 'Exercício básico para abdominais'],
                ['nome' => 'Prancha', 'popular' => true, 'descricao' => 'Exercício isométrico para core'],
                ['nome' => 'Abdominal Oblíquo', 'popular' => true, 'descricao' => 'Foco nos músculos oblíquos'],
                ['nome' => 'Russian Twist', 'popular' => false, 'descricao' => 'Rotação do tronco'],
                ['nome' => 'Bicicleta', 'popular' => true, 'descricao' => 'Movimento alternado'],
                ['nome' => 'Elevação de Pernas', 'popular' => false, 'descricao' => 'Foco na porção inferior'],
                ['nome' => 'Dead Bug', 'popular' => false, 'descricao' => 'Exercício de estabilização'],
            ],

            // ❤️ CARDIO
            'cardio' => [
                ['nome' => 'Esteira', 'popular' => true, 'descricao' => 'Caminhada ou corrida'],
                ['nome' => 'Bicicleta', 'popular' => true, 'descricao' => 'Exercício cardiovascular de baixo impacto'],
                ['nome' => 'Elíptico', 'popular' => true, 'descricao' => 'Movimento completo de baixo impacto'],
                ['nome' => 'Pular Corda', 'popular' => false, 'descricao' => 'Exercício intenso de coordenação'],
                ['nome' => 'Burpee', 'popular' => true, 'descricao' => 'Exercício funcional intenso'],
                ['nome' => 'Mountain Climber', 'popular' => false, 'descricao' => 'Exercício dinâmico para cardio'],
                ['nome' => 'Jumping Jack', 'popular' => false, 'descricao' => 'Exercício de aquecimento'],
            ],

            // 🤸 FUNCIONAL
            'funcional' => [
                ['nome' => 'Kettlebell Swing', 'popular' => true, 'descricao' => 'Exercício explosivo com kettlebell'],
                ['nome' => 'Turkish Get Up', 'popular' => false, 'descricao' => 'Movimento complexo de mobilidade'],
                ['nome' => 'Medicine Ball Slam', 'popular' => false, 'descricao' => 'Exercício explosivo'],
                ['nome' => 'Box Jump', 'popular' => true, 'descricao' => 'Salto na caixa'],
                ['nome' => 'Battle Rope', 'popular' => false, 'descricao' => 'Exercício com cordas'],
                ['nome' => 'Farmer Walk', 'popular' => false, 'descricao' => 'Caminhada com peso'],
                ['nome' => 'TRX', 'popular' => true, 'descricao' => 'Exercícios suspensos'],
            ],
        ];

        $totalCriados = 0;

        foreach ($exercicios as $grupoSlug => $listaExercicios) {
            $grupo = $grupos->get($grupoSlug);
            
            if (!$grupo) {
                $this->command->warn("⚠️ Grupo '{$grupoSlug}' não encontrado!");
                continue;
            }

            foreach ($listaExercicios as $exercicio) {
                ExercicioTemplate::updateOrCreate(
                    [
                        'nome' => $exercicio['nome'],
                        'grupo_muscular_id' => $grupo->id
                    ],
                    [
                        'descricao' => $exercicio['descricao'],
                        'popular' => $exercicio['popular'],
                        'ativo' => true
                    ]
                );
                $totalCriados++;
            }

            $this->command->info("✅ {$grupo->nome}: " . count($listaExercicios) . " exercícios");
        }

        $this->command->info('');
        $this->command->info('🎉 Templates de exercícios criados com sucesso!');
        $this->command->info("📊 Total: {$totalCriados} exercícios");
        $this->command->info('⭐ Populares: ' . ExercicioTemplate::where('popular', true)->count());
    }
}