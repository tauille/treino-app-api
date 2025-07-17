<?php

namespace Database\Seeders;

use App\Models\GrupoMuscular;
use Illuminate\Database\Seeder;

class GrupoMuscularSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupos = [
            [
                'nome' => 'Peito',
                'slug' => 'peito',
                'icone' => '💪',
                'cor' => '#FF6B6B',
                'descricao' => 'Exercícios para desenvolvimento do peitoral maior e menor',
                'ordem' => 1,
                'ativo' => true,
            ],
            [
                'nome' => 'Costas',
                'slug' => 'costas',
                'icone' => '🏋️',
                'cor' => '#4ECDC4',
                'descricao' => 'Exercícios para latíssimo, romboides, trapézio e músculos das costas',
                'ordem' => 2,
                'ativo' => true,
            ],
            [
                'nome' => 'Pernas',
                'slug' => 'pernas',
                'icone' => '🦵',
                'cor' => '#45B7D1',
                'descricao' => 'Exercícios para quadríceps, glúteos, isquiotibiais e panturrilhas',
                'ordem' => 3,
                'ativo' => true,
            ],
            [
                'nome' => 'Ombros',
                'slug' => 'ombros',
                'icone' => '💪',
                'cor' => '#96CEB4',
                'descricao' => 'Exercícios para deltoides anterior, posterior e lateral',
                'ordem' => 4,
                'ativo' => true,
            ],
            [
                'nome' => 'Braços',
                'slug' => 'bracos',
                'icone' => '💪',
                'cor' => '#FECA57',
                'descricao' => 'Exercícios para bíceps, tríceps e antebraços',
                'ordem' => 5,
                'ativo' => true,
            ],
            [
                'nome' => 'Core',
                'slug' => 'core',
                'icone' => '🎯',
                'cor' => '#FF9FF3',
                'descricao' => 'Exercícios para abdominais, core e estabilização',
                'ordem' => 6,
                'ativo' => true,
            ],
            [
                'nome' => 'Cardio',
                'slug' => 'cardio',
                'icone' => '❤️',
                'cor' => '#54A0FF',
                'descricao' => 'Exercícios cardiovasculares e de resistência',
                'ordem' => 7,
                'ativo' => true,
            ],
            [
                'nome' => 'Funcional',
                'slug' => 'funcional',
                'icone' => '🤸',
                'cor' => '#5F27CD',
                'descricao' => 'Exercícios funcionais e de mobilidade',
                'ordem' => 8,
                'ativo' => true,
            ],
            [
                'nome' => 'Outro',
                'slug' => 'outro',
                'icone' => '➕',
                'cor' => '#6C5CE7',
                'descricao' => 'Outros tipos de exercícios personalizados',
                'ordem' => 99,
                'ativo' => true,
            ],
        ];

        foreach ($grupos as $grupo) {
            GrupoMuscular::updateOrCreate(
                ['slug' => $grupo['slug']], // Buscar por slug
                $grupo // Atualizar ou criar com estes dados
            );
        }

        $this->command->info('✅ Grupos musculares criados com sucesso!');
        $this->command->info('📊 Total: ' . GrupoMuscular::count() . ' grupos');
    }
}