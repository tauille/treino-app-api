<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExercicioTemplate extends Model
{
    use HasFactory;

    protected $table = 'exercicios_templates';

    protected $fillable = [
        'nome',
        'grupo_muscular_id',
        'descricao',
        'instrucoes',
        'imagem_url',
        'popular',
        'ativo'
    ];

    protected $casts = [
        'popular' => 'boolean',
        'ativo' => 'boolean'
    ];

    // ========================================
    // RELACIONAMENTOS
    // ========================================

    /**
     * Grupo muscular do template
     */
    public function grupoMuscular(): BelongsTo
    {
        return $this->belongsTo(GrupoMuscular::class);
    }

    /**
     * Exercícios criados baseados neste template
     */
    public function exercicios(): HasMany
    {
        return $this->hasMany(Exercicio::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope para templates ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para templates populares
     */
    public function scopePopular($query)
    {
        return $query->where('popular', true);
    }

    /**
     * Scope para filtrar por grupo muscular
     */
    public function scopeDoGrupo($query, $grupoId)
    {
        return $query->where('grupo_muscular_id', $grupoId);
    }

    /**
     * Scope para busca por nome
     */
    public function scopeBusca($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('descricao', 'like', "%{$termo}%");
        });
    }

    /**
     * Scope para ordenação padrão
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('popular', 'desc')
                    ->orderBy('nome');
    }

    /**
     * Scope para incluir dados do grupo
     */
    public function scopeComGrupo($query)
    {
        return $query->with('grupoMuscular:id,nome,slug,icone,cor');
    }

    // ========================================
    // ACESSORS
    // ========================================

    /**
     * Retorna nome do grupo muscular
     */
    public function getNomeGrupoAttribute()
    {
        return $this->grupoMuscular?->nome;
    }

    /**
     * Retorna ícone do grupo muscular
     */
    public function getIconeGrupoAttribute()
    {
        return $this->grupoMuscular?->icone;
    }

    /**
     * Retorna cor do grupo muscular
     */
    public function getCorGrupoAttribute()
    {
        return $this->grupoMuscular?->cor;
    }

    /**
     * Retorna badge de popular
     */
    public function getBadgePopularAttribute()
    {
        return $this->popular ? '⭐ Popular' : null;
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Retorna quantidade de vezes que foi usado
     */
    public function getTotalUsosAttribute()
    {
        return $this->exercicios()->count();
    }

    /**
     * Verifica se tem imagem
     */
    public function hasImagem(): bool
    {
        return !empty($this->imagem_url);
    }

    /**
     * Verifica se é popular
     */
    public function isPopular(): bool
    {
        return $this->popular === true;
    }

    /**
     * Marca como popular
     */
    public function marcarComoPopular(): bool
    {
        return $this->update(['popular' => true]);
    }

    /**
     * Remove marca de popular
     */
    public function removerPopular(): bool
    {
        return $this->update(['popular' => false]);
    }

    /**
     * Criar exercício baseado neste template
     */
    public function criarExercicio(array $dados): Exercicio
    {
        return Exercicio::create(array_merge([
            'nome_exercicio' => $this->nome,
            'descricao' => $this->descricao,
            'grupo_muscular_id' => $this->grupo_muscular_id,
            'exercicio_template_id' => $this->id,
        ], $dados));
    }
}