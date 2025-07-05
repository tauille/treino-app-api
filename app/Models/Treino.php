<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treino extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nome_treino',
        'tipo_treino',
        'descricao',     // NOVO: descrição do treino
        'dificuldade',   // NOVO: iniciante, intermediário, avançado
        'status',        // NOVO: ativo, inativo
    ];

    /**
     * Relacionamento com usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com exercícios
     */
    public function exercicios()
    {
        return $this->hasMany(Exercicio::class)->orderBy('ordem');
    }

    /**
     * NOVO: Relacionamento com exercícios ativos apenas (MELHORA PERFORMANCE)
     */
    public function exerciciosAtivos()
    {
        return $this->hasMany(Exercicio::class)->where('status', 'ativo')->orderBy('ordem');
    }

    /**
     * Retorna o número total de exercícios do treino
     */
    public function getTotalExerciciosAttribute(): int
    {
        return $this->exercicios()->count();
    }

    /**
     * NOVO: Retorna o número total de exercícios ativos
     */
    public function getTotalExerciciosAtivosAttribute(): int
    {
        return $this->exerciciosAtivos()->count();
    }

    /**
     * MELHORADO: Retorna a duração estimada do treino em minutos
     */
    public function getDuracaoEstimadaAttribute(): int
    {
        $totalSegundos = 0;
        
        foreach ($this->exerciciosAtivos as $exercicio) {
            $series = $exercicio->series ?: 1;
            
            // Tempo de execução
            if ($exercicio->tipo_execucao === 'tempo' && $exercicio->tempo_execucao) {
                $totalSegundos += $exercicio->tempo_execucao * $series;
            } else {
                // Para repetições, estimamos 2 segundos por repetição
                $totalSegundos += (($exercicio->repeticoes ?? 0) * 2) * $series;
            }
            
            // Tempo de descanso entre séries (séries - 1)
            if ($series > 1) {
                $totalSegundos += $exercicio->tempo_descanso * ($series - 1);
            }
            
            // Tempo de descanso após o exercício
            $totalSegundos += $exercicio->tempo_descanso;
        }

        return ceil($totalSegundos / 60); // Converte para minutos
    }

    /**
     * NOVO: Retorna a duração formatada (ex: "45 min", "1h 30min") - MUITO MELHOR UX!
     */
    public function getDuracaoFormatadaAttribute(): string
    {
        $minutos = $this->duracao_estimada;
        
        if ($minutos >= 60) {
            $horas = floor($minutos / 60);
            $minutosRestantes = $minutos % 60;
            return $horas . 'h' . ($minutosRestantes > 0 ? ' ' . $minutosRestantes . 'min' : '');
        }
        
        return $minutos . ' min';
    }

    /**
     * NOVO: Retorna os grupos musculares trabalhados no treino
     */
    public function getGruposMuscularesTextoAttribute(): string
    {
        $grupos = $this->exerciciosAtivos()
            ->whereNotNull('grupo_muscular')
            ->pluck('grupo_muscular')
            ->unique()
            ->filter()
            ->values()
            ->toArray();
        
        if (empty($grupos)) {
            return 'Não especificado';
        }
        
        return implode(', ', $grupos);
    }

    /**
     * NOVO: Retorna a cor baseada na dificuldade (para UI)
     */
    public function getCorDificuldadeAttribute(): string
    {
        return match($this->dificuldade) {
            'iniciante' => 'text-green-600',
            'intermediario' => 'text-yellow-600',
            'avancado' => 'text-red-600',
            default => 'text-gray-600'
        };
    }

    /**
     * NOVO: Retorna o texto da dificuldade formatado
     */
    public function getDificuldadeTextoAttribute(): string
    {
        return match($this->dificuldade) {
            'iniciante' => 'Iniciante',
            'intermediario' => 'Intermediário',
            'avancado' => 'Avançado',
            default => 'Não definido'
        };
    }

    /**
     * Scope para buscar treinos de um usuário específico
     */
    public function scopeOfUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para buscar por tipo de treino
     */
    public function scopeOfType($query, $tipo)
    {
        return $query->where('tipo_treino', 'like', '%' . $tipo . '%');
    }

    /**
     * NOVO: Scope para treinos ativos (ESSENCIAL!)
     */
    public function scopeAtivo($query)
    {
        return $query->where('status', 'ativo');
    }

    /**
     * NOVO: Scope para treinos por dificuldade
     */
    public function scopeDificuldade($query, $dificuldade)
    {
        return $query->where('dificuldade', $dificuldade);
    }

    /**
     * NOVO: Scope para busca por nome/descrição (MUITO ÚTIL!)
     */
    public function scopeBusca($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('nome_treino', 'like', '%' . $termo . '%')
              ->orWhere('descricao', 'like', '%' . $termo . '%')
              ->orWhere('tipo_treino', 'like', '%' . $termo . '%');
        });
    }

    /**
     * NOVO: Boot method para definir valores padrão
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($treino) {
            if (!$treino->status) {
                $treino->status = 'ativo';
            }
        });
    }
}