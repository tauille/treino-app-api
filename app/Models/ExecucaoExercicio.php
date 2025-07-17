<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecucaoExercicio extends Model
{
    use HasFactory;

    protected $table = 'execucao_exercicios';

    protected $fillable = [
        'execucao_treino_id',
        'exercicio_id',
        'status',
        'ordem_execucao',
        'data_inicio',
        'data_fim',
        'tempo_executado_segundos',
        'series_realizadas',
        'repeticoes_realizadas',
        'peso_utilizado',
        'unidade_peso',
        'tempo_descanso_realizado',
        'series_planejadas',
        'repeticoes_planejadas',
        'peso_planejado',
        'tempo_execucao_planejado',
        'tempo_descanso_planejado',
        'tipo_execucao',
        'observacoes',
        'dados_extras',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'dados_extras' => 'array',
        'peso_utilizado' => 'decimal:2',
        'peso_planejado' => 'decimal:2',
    ];

    // Relacionamentos
    public function execucaoTreino(): BelongsTo
    {
        return $this->belongsTo(ExecucaoTreino::class);
    }

    public function exercicio(): BelongsTo
    {
        return $this->belongsTo(Exercicio::class);
    }

    // Scopes
    public function scopeCompletados($query)
    {
        return $query->where('status', 'completado');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopePorOrdem($query)
    {
        return $query->orderBy('ordem_execucao');
    }

    // Métodos auxiliares
    public function isCompletado(): bool
    {
        return $this->status === 'completado';
    }

    public function isEmAndamento(): bool
    {
        return $this->status === 'em_andamento';
    }

    public function isPulado(): bool
    {
        return $this->status === 'pulado';
    }

    public function getTempoExecutadoFormatado(): string
    {
        $segundos = $this->tempo_executado_segundos;
        $minutos = floor($segundos / 60);
        $segundos = $segundos % 60;

        return sprintf('%02d:%02d', $minutos, $segundos);
    }

    public function getProgressoSeries(): string
    {
        if (!$this->series_planejadas) {
            return 'N/A';
        }

        $realizadas = $this->series_realizadas ?? 0;
        return "{$realizadas}/{$this->series_planejadas}";
    }

    public function getProgressoRepeticoes(): string
    {
        if (!$this->repeticoes_planejadas) {
            return 'N/A';
        }

        $realizadas = $this->repeticoes_realizadas ?? 0;
        return "{$realizadas}/{$this->repeticoes_planejadas}";
    }

    public function getDiferencaPeso(): ?float
    {
        if (!$this->peso_planejado || !$this->peso_utilizado) {
            return null;
        }

        return $this->peso_utilizado - $this->peso_planejado;
    }

    public function getPerformance(): string
    {
        // Calcula performance baseada em repetições/tempo planejado vs realizado
        if ($this->tipo_execucao === 'repeticao') {
            if (!$this->repeticoes_planejadas || !$this->repeticoes_realizadas) {
                return 'N/A';
            }

            $percentual = ($this->repeticoes_realizadas / $this->repeticoes_planejadas) * 100;
            
            if ($percentual >= 100) {
                return 'Excelente';
            } elseif ($percentual >= 80) {
                return 'Bom';
            } elseif ($percentual >= 60) {
                return 'Regular';
            } else {
                return 'Abaixo do esperado';
            }
        }

        if ($this->tipo_execucao === 'tempo') {
            if (!$this->tempo_execucao_planejado || !$this->tempo_executado_segundos) {
                return 'N/A';
            }

            $percentual = ($this->tempo_executado_segundos / $this->tempo_execucao_planejado) * 100;
            
            if ($percentual >= 90 && $percentual <= 110) {
                return 'Excelente';
            } elseif ($percentual >= 80 && $percentual <= 120) {
                return 'Bom';
            } else {
                return 'Regular';
            }
        }

        return 'N/A';
    }

    // Métodos de controle
    public function iniciar(): bool
    {
        if ($this->status !== 'nao_iniciado') {
            return false;
        }

        $this->update([
            'status' => 'em_andamento',
            'data_inicio' => now(),
        ]);

        return true;
    }

    public function completar(array $dados = []): bool
    {
        if ($this->status !== 'em_andamento') {
            return false;
        }

        $updateData = array_merge([
            'status' => 'completado',
            'data_fim' => now(),
        ], $dados);

        $this->update($updateData);
        return true;
    }

    public function pular(string $motivo = null): bool
    {
        if ($this->isCompletado()) {
            return false;
        }

        $dadosExtras = $this->dados_extras ?? [];
        if ($motivo) {
            $dadosExtras['motivo_pulo'] = $motivo;
        }

        $this->update([
            'status' => 'pulado',
            'data_fim' => now(),
            'dados_extras' => $dadosExtras,
        ]);

        return true;
    }

    public function reiniciar(): bool
    {
        if ($this->status === 'nao_iniciado') {
            return false;
        }

        $this->update([
            'status' => 'nao_iniciado',
            'data_inicio' => null,
            'data_fim' => null,
            'tempo_executado_segundos' => 0,
            'series_realizadas' => null,
            'repeticoes_realizadas' => null,
        ]);

        return true;
    }

    public static function criarPorExercicio(ExecucaoTreino $execucaoTreino, Exercicio $exercicio, int $ordem): self
    {
        return self::create([
            'execucao_treino_id' => $execucaoTreino->id,
            'exercicio_id' => $exercicio->id,
            'ordem_execucao' => $ordem,
            'series_planejadas' => $exercicio->series,
            'repeticoes_planejadas' => $exercicio->repeticoes,
            'peso_planejado' => $exercicio->peso,
            'tempo_execucao_planejado' => $exercicio->tempo_execucao,
            'tempo_descanso_planejado' => $exercicio->tempo_descanso,
            'tipo_execucao' => $exercicio->tipo_execucao,
        ]);
    }
}