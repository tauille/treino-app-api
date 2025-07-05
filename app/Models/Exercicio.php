<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercicio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'treino_id',
        'nome_exercicio',
        'tipo_execucao',
        'repeticoes',
        'tempo_execucao',
        'tempo_descanso',
        'imagem_path',
        'descricao',
        'ordem',
        // Novos campos opcionais
        'grupo_muscular',
        'series',
        'peso',
        'unidade_peso',
        'observacoes',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'repeticoes' => 'integer',
        'tempo_execucao' => 'integer',
        'tempo_descanso' => 'integer',
        'ordem' => 'integer',
        'series' => 'integer',
        'peso' => 'decimal:2',
    ];

    /**
     * Relacionamento com treino
     */
    public function treino()
    {
        return $this->belongsTo(Treino::class);
    }

    /**
     * Retorna o texto de execução formatado (seu método original)
     */
    public function getTextoExecucaoAttribute(): string
    {
        if ($this->tipo_execucao === 'repeticao') {
            $texto = $this->repeticoes . ' repetições';
            if ($this->series && $this->series > 1) {
                $texto = $this->series . 'x ' . $texto;
            }
            if ($this->peso) {
                $texto .= " ({$this->peso}{$this->unidade_peso})";
            }
            return $texto;
        } else {
            return $this->tempo_execucao . ' segundos';
        }
    }

    /**
     * Retorna o texto do tempo de descanso formatado (seu método original)
     */
    public function getTextoDescansoAttribute(): string
    {
        $minutos = floor($this->tempo_descanso / 60);
        $segundos = $this->tempo_descanso % 60;

        if ($minutos > 0) {
            return $minutos . 'm' . ($segundos > 0 ? ' ' . $segundos . 's' : '');
        } else {
            return $segundos . 's';
        }
    }

    /**
     * Retorna a URL completa da imagem (seu método original)
     */
    public function getImagemUrlAttribute(): ?string
    {
        if (!$this->imagem_path) {
            return null;
        }

        return url('storage/' . $this->imagem_path);
    }

    /**
     * Retorna o tempo total estimado do exercício em segundos (novo método útil)
     */
    public function getTempoTotalEstimadoAttribute(): int
    {
        $tempoExecucao = 0;
        $series = $this->series ?: 1;
        
        if ($this->tipo_execucao === 'tempo') {
            $tempoExecucao = $this->tempo_execucao * $series;
        } else {
            // Para repetições, estimamos 2 segundos por repetição
            $tempoExecucao = ($this->repeticoes * 2) * $series;
        }
        
        // Adiciona tempo de descanso entre séries (séries - 1)
        $tempoDescanso = $this->tempo_descanso * ($series - 1);
        
        return $tempoExecucao + $tempoDescanso;
    }

    /**
     * Scope para ordenar por ordem (seu método original)
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordem');
    }

    /**
     * Scope para buscar exercícios de um treino específico (seu método original)
     */
    public function scopeOfTreino($query, $treinoId)
    {
        return $query->where('treino_id', $treinoId);
    }

    /**
     * Scope para buscar exercícios por tipo de execução (seu método original)
     */
    public function scopeByTipoExecucao($query, $tipo)
    {
        return $query->where('tipo_execucao', $tipo);
    }

    /**
     * Novos scopes úteis
     */
    public function scopeAtivo($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeGrupoMuscular($query, $grupo)
    {
        return $query->where('grupo_muscular', $grupo);
    }

    /**
     * Boot method para definir ordem automaticamente (seu método original - MANTIDO)
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($exercicio) {
            if (!$exercicio->ordem) {
                $maxOrdem = static::where('treino_id', $exercicio->treino_id)->max('ordem');
                $exercicio->ordem = ($maxOrdem ?? 0) + 1;
            }
            
            // Define valores padrão para novos campos
            if (!$exercicio->series) {
                $exercicio->series = 1;
            }
            if (!$exercicio->status) {
                $exercicio->status = 'ativo';
            }
        });
    }
}