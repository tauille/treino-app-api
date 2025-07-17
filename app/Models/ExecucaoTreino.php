<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class ExecucaoTreino extends Model
{
    use HasFactory;

    protected $table = 'execucao_treinos';

    protected $fillable = [
        'user_id',
        'treino_id',
        'status',
        'data_inicio',
        'data_fim',
        'tempo_total_segundos',
        'tempo_pausado_segundos',
        'total_exercicios',
        'exercicios_completados',
        'exercicio_atual_id',
        'exercicio_atual_ordem',
        'observacoes',
        'dados_extras',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'dados_extras' => 'array',
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function treino(): BelongsTo
    {
        return $this->belongsTo(Treino::class);
    }

    public function execucaoExercicios(): HasMany
    {
        return $this->hasMany(ExecucaoExercicio::class);
    }

    public function exercicioAtual(): BelongsTo
    {
        return $this->belongsTo(Exercicio::class, 'exercicio_atual_id');
    }

    // Scopes
    public function scopeOfUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEmAndamento($query)
    {
        return $query->whereIn('status', ['iniciado', 'pausado']);
    }

    public function scopeFinalizados($query)
    {
        return $query->where('status', 'finalizado');
    }

    public function scopeHoje($query)
    {
        return $query->whereDate('data_inicio', Carbon::today());
    }

    public function scopeUltimos30Dias($query)
    {
        return $query->where('data_inicio', '>=', Carbon::now()->subDays(30));
    }

    // Métodos auxiliares
    public function isEmAndamento(): bool
    {
        return in_array($this->status, ['iniciado', 'pausado']);
    }

    public function isFinalizado(): bool
    {
        return $this->status === 'finalizado';
    }

    public function isPausado(): bool
    {
        return $this->status === 'pausado';
    }

    public function getTempoTotalFormatado(): string
    {
        $segundos = $this->tempo_total_segundos;
        $horas = floor($segundos / 3600);
        $minutos = floor(($segundos % 3600) / 60);
        $segundos = $segundos % 60;

        if ($horas > 0) {
            return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
        }

        return sprintf('%02d:%02d', $minutos, $segundos);
    }

    public function getProgressoPercentual(): float
    {
        if ($this->total_exercicios == 0) {
            return 0;
        }

        return round(($this->exercicios_completados / $this->total_exercicios) * 100, 2);
    }

    public function getDuracaoReal(): ?string
    {
        if (!$this->data_fim) {
            return null;
        }

        $duracao = $this->data_inicio->diffInSeconds($this->data_fim);
        $horas = floor($duracao / 3600);
        $minutos = floor(($duracao % 3600) / 60);
        $segundos = $duracao % 60;

        if ($horas > 0) {
            return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
        }

        return sprintf('%02d:%02d', $minutos, $segundos);
    }

    public function getTempoEfetivo(): int
    {
        // Tempo total menos tempo pausado
        return max(0, $this->tempo_total_segundos - $this->tempo_pausado_segundos);
    }

    public function proximoExercicio(): ?Exercicio
    {
        return Exercicio::where('treino_id', $this->treino_id)
            ->where('ordem', '>', $this->exercicio_atual_ordem)
            ->where('status', 'ativo')
            ->orderBy('ordem')
            ->first();
    }

    public function exercicioAnterior(): ?Exercicio
    {
        return Exercicio::where('treino_id', $this->treino_id)
            ->where('ordem', '<', $this->exercicio_atual_ordem)
            ->where('status', 'ativo')
            ->orderBy('ordem', 'desc')
            ->first();
    }

    // Métodos de controle
    public function pausar(): bool
    {
        if ($this->status !== 'iniciado') {
            return false;
        }

        $this->update(['status' => 'pausado']);
        return true;
    }

    public function retomar(): bool
    {
        if ($this->status !== 'pausado') {
            return false;
        }

        $this->update(['status' => 'iniciado']);
        return true;
    }

    public function finalizar(): bool
    {
        if (!$this->isEmAndamento()) {
            return false;
        }

        $this->update([
            'status' => 'finalizado',
            'data_fim' => now(),
        ]);

        return true;
    }

    public function cancelar(): bool
    {
        if (!$this->isEmAndamento()) {
            return false;
        }

        $this->update([
            'status' => 'cancelado',
            'data_fim' => now(),
        ]);

        return true;
    }

    public function avancarExercicio(): bool
    {
        $proximoExercicio = $this->proximoExercicio();
        
        if (!$proximoExercicio) {
            // Não há próximo exercício, finalizar treino
            return $this->finalizar();
        }

        $this->update([
            'exercicio_atual_id' => $proximoExercicio->id,
            'exercicio_atual_ordem' => $proximoExercicio->ordem,
        ]);

        return true;
    }

    public function voltarExercicio(): bool
    {
        $exercicioAnterior = $this->exercicioAnterior();
        
        if (!$exercicioAnterior) {
            return false;
        }

        $this->update([
            'exercicio_atual_id' => $exercicioAnterior->id,
            'exercicio_atual_ordem' => $exercicioAnterior->ordem,
        ]);

        return true;
    }
}