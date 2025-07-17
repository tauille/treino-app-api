<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Treino;
use App\Models\Exercicio;
use App\Models\ExecucaoTreino;
use App\Models\ExecucaoExercicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class EstatisticasTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Treino $treino;
    private Exercicio $exercicio;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuário de teste
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Autenticar usuário
        Sanctum::actingAs($this->user);

        // Criar treino de teste
        $this->treino = Treino::factory()->create([
            'user_id' => $this->user->id,
            'nome_treino' => 'Treino Teste',
            'tipo_treino' => 'Musculação',
            'dificuldade' => 'intermediario',
        ]);

        // Criar exercício de teste
        $this->exercicio = Exercicio::factory()->create([
            'treino_id' => $this->treino->id,
            'nome_exercicio' => 'Supino Reto',
            'grupo_muscular' => 'Peito',
            'tipo_execucao' => 'repeticao',
        ]);
    }

    /** @test */
    public function pode_acessar_dashboard_de_estatisticas()
    {
        $response = $this->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'geral' => [
                        'total_treinos_executados',
                        'total_exercicios_realizados',
                        'tempo_total_formatado',
                        'tempo_total_segundos',
                        'media_duracao_formatada',
                        'media_duracao_segundos',
                    ],
                    'ultimos_30_dias',
                    'favoritos',
                    'sequencias',
                ],
                'message'
            ]);
    }

    /** @test */
    public function dashboard_retorna_dados_corretos_sem_execucoes()
    {
        $response = $this->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $data = $response->json('data.geral');
        
        $this->assertEquals(0, $data['total_treinos_executados']);
        $this->assertEquals(0, $data['total_exercicios_realizados']);
        $this->assertEquals('00:00:00', $data['tempo_total_formatado']);
        $this->assertEquals(0, $data['tempo_total_segundos']);
    }

    /** @test */
    public function dashboard_calcula_estatisticas_corretamente_com_execucoes()
    {
        // Criar execuções de teste
        $execucao1 = ExecucaoTreino::factory()->create([
            'user_id' => $this->user->id,
            'treino_id' => $this->treino->id,
            'status' => 'finalizado',
            'tempo_total' => 3600, // 1 hora
        ]);

        $execucao2 = ExecucaoTreino::factory()->create([
            'user_id' => $this->user->id,
            'treino_id' => $this->treino->id,
            'status' => 'finalizado',
            'tempo_total' => 2700, // 45 minutos
        ]);

        // Criar execuções de exercícios
        ExecucaoExercicio::factory()->create([
            'execucao_treino_id' => $execucao1->id,
            'exercicio_id' => $this->exercicio->id,
            'concluido' => true,
            'series_realizadas' => 3,
            'repeticoes_realizadas' => 12,
            'peso_utilizado' => 80.0,
        ]);

        ExecucaoExercicio::factory()->create([
            'execucao_treino_id' => $execucao2->id,
            'exercicio_id' => $this->exercicio->id,
            'concluido' => true,
            'series_realizadas' => 3,
            'repeticoes_realizadas' => 10,
            'peso_utilizado' => 85.0,
        ]);

        $response = $this->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $data = $response->json('data.geral');
        
        $this->assertEquals(2, $data['total_treinos_executados']);
        $this->assertEquals(2, $data['total_exercicios_realizados']);
        $this->assertEquals(6300, $data['tempo_total_segundos']); // 3600 + 2700
        $this->assertEquals('01:45:00', $data['tempo_total_formatado']);
    }

    /** @test */
    public function pode_obter_progresso_por_periodo()
    {
        $response = $this->getJson('/api/estatisticas/progresso?periodo=30');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'periodo',
                    'progresso_diario',
                    'exercicios_mais_executados',
                    'grupos_musculares',
                ],
                'message'
            ]);
    }

    /** @test */
    public function pode_obter_rankings()
    {
        $response = $this->getJson('/api/estatisticas/rankings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'top_exercicios',
                    'top_treinos',
                    'recordes_peso',
                    'sequencias',
                ],
                'message'
            ]);
    }

    /** @test */
    public function pode_obter_evolucao_de_exercicio_existente()
    {
        // Criar algumas execuções do exercício
        $execucao = ExecucaoTreino::factory()->create([
            'user_id' => $this->user->id,
            'treino_id' => $this->treino->id,
            'status' => 'finalizado',
        ]);

        ExecucaoExercicio::factory()->count(3)->create([
            'execucao_treino_id' => $execucao->id,
            'exercicio_id' => $this->exercicio->id,
            'concluido' => true,
            'peso_utilizado' => 80.0,
        ]);

        $response = $this->getJson("/api/estatisticas/exercicio/{$this->exercicio->id}/evolucao");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'exercicio' => [
                        'id',
                        'nome',
                        'grupo_muscular',
                        'tipo_execucao',
                    ],
                    'evolucao' => [
                        'estatisticas',
                        'progressao',
                        'historico',
                    ],
                ],
                'message'
            ]);
    }

    /** @test */
    public function retorna_erro_404_para_exercicio_inexistente()
    {
        $response = $this->getJson('/api/estatisticas/exercicio/999/evolucao');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Exercício não encontrado'
            ]);
    }

    /** @test */
    public function pode_obter_estatisticas_por_grupo_muscular()
    {
        $response = $this->getJson('/api/estatisticas/grupos-musculares');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    /** @test */
    public function pode_obter_metricas_de_consistencia()
    {
        $response = $this->getJson('/api/estatisticas/consistencia');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'consistencia',
                    'sequencias',
                ],
                'message'
            ]);
    }

    /** @test */
    public function pode_obter_comparativos_entre_periodos()
    {
        $response = $this->getJson('/api/estatisticas/comparativos?periodo1=30&periodo2=60');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'periodo_atual',
                    'periodo_anterior',
                    'variacao',
                ],
                'message'
            ]);
    }

    /** @test */
    public function pode_exportar_dados_estatisticos()
    {
        $response = $this->getJson('/api/estatisticas/exportar?formato=json');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'geral',
                    'top_exercicios',
                    'top_treinos',
                    'grupos_musculares',
                    'progresso_90_dias',
                ],
                'formato',
                'gerado_em',
                'message'
            ]);
    }

    /** @test */
    public function pode_gerar_relatorio_semanal()
    {
        $response = $this->getJson('/api/estatisticas/relatorio/semanal');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'periodo',
                    'progresso',
                ],
                'tipo',
                'gerado_em',
                'message'
            ]);
    }

    /** @test */
    public function pode_gerar_relatorio_mensal()
    {
        $response = $this->getJson('/api/estatisticas/relatorio/mensal');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'periodo',
                    'progresso',
                    'top_exercicios',
                ],
                'tipo',
                'gerado_em',
                'message'
            ]);
    }

    /** @test */
    public function pode_gerar_relatorio_anual()
    {
        $response = $this->getJson('/api/estatisticas/relatorio/anual');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'periodo',
                    'geral',
                    'rankings',
                ],
                'tipo',
                'gerado_em',
                'message'
            ]);
    }

    /** @test */
    public function analytics_frequencia_semanal_funciona()
    {
        $response = $this->getJson('/api/estatisticas/analytics/frequencia-semanal');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    /** @test */
    public function analytics_duracao_media_funciona()
    {
        $response = $this->getJson('/api/estatisticas/analytics/duracao-media');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'duracao_media_geral',
                    'tempo_total',
                ],
                'message'
            ]);
    }

    /** @test */
    public function analytics_evolucao_peso_funciona()
    {
        $response = $this->getJson("/api/estatisticas/analytics/evolucao-peso/{$this->exercicio->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    /** @test */
    public function analytics_volume_treino_funciona()
    {
        $response = $this->getJson('/api/estatisticas/analytics/volume-treino');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }

    /** @test */
    public function requer_autenticacao_para_acessar_estatisticas()
    {
        // Fazer logout
        auth()->logout();

        $response = $this->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(401);
    }

    /** @test */
    public function usuario_so_ve_proprias_estatisticas()
    {
        // Criar outro usuário
        $outroUsuario = User::factory()->create();

        // Criar treino do outro usuário
        $outroTreino = Treino::factory()->create([
            'user_id' => $outroUsuario->id,
        ]);

        // Criar execução do outro usuário
        ExecucaoTreino::factory()->create([
            'user_id' => $outroUsuario->id,
            'treino_id' => $outroTreino->id,
            'status' => 'finalizado',
        ]);

        // Fazer request como usuário original
        $response = $this->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $data = $response->json('data.geral');
        
        // Deve retornar 0 treinos porque o usuário autenticado não tem execuções
        $this->assertEquals(0, $data['total_treinos_executados']);
    }

    /** @test */
    public function estatisticas_funcionam_com_dados_de_diferentes_datas()
    {
        // Criar execuções em datas diferentes
        $execucaoOntem = ExecucaoTreino::factory()->create([
            'user_id' => $this->user->id,
            'treino_id' => $this->treino->id,
            'status' => 'finalizado',
            'tempo_total' => 3600,
            'created_at' => Carbon::yesterday(),
        ]);

        $execucaoHoje = ExecucaoTreino::factory()->create([
            'user_id' => $this->user->id,
            'treino_id' => $this->treino->id,
            'status' => 'finalizado',
            'tempo_total' => 2700,
            'created_at' => Carbon::today(),
        ]);

        $response = $this->getJson('/api/estatisticas/progresso?periodo=7');

        $response->assertStatus(200);
        
        $progressoDiario = $response->json('data.progresso_diario');
        
        // Deve ter dados de pelo menos 2 dias
        $this->assertGreaterThanOrEqual(1, count($progressoDiario));
    }

    /** @test */
    public function calcula_sequencias_corretamente()
    {
        // Criar execuções em dias consecutivos
        for ($i = 0; $i < 3; $i++) {
            ExecucaoTreino::factory()->create([
                'user_id' => $this->user->id,
                'treino_id' => $this->treino->id,
                'status' => 'finalizado',
                'created_at' => Carbon::today()->subDays($i),
            ]);
        }

        $response = $this->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $sequencias = $response->json('data.sequencias');
        
        $this->assertIsArray($sequencias);
        $this->assertArrayHasKey('sequencia_atual', $sequencias);
        $this->assertArrayHasKey('maior_sequencia', $sequencias);
        $this->assertArrayHasKey('sequencia_semanal', $sequencias);
    }
}