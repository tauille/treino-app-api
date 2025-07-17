<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoMuscular extends Model
{
    use HasFactory;

    protected $table = 'grupos_musculares';

    protected $fillable = [
        'nome',
        'slug',
        'icone',
        'cor',
        'descricao',
        'ordem',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer'
    ];

    // ========================================
    // RELACIONAMENTOS
    // ========================================

    /**
     * Exercícios que pertencem a este grupo muscular
     */
    public function exercicios(): HasMany
    {
        return $this->hasMany(Exercicio::class);
    }

    /**
     * Templates de exercícios deste grupo
     */
    public function exerciciosTemplates(): HasMany
    {
        return $this->hasMany(ExercicioTemplate::class);
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope para grupos ativos
     */
    public function scopeAtivo($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenação padrão
     */
    public function scopeOrdenado($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Scope para busca por nome
     */
    public function scopeBusca($query, $termo)
    {
        return $query->where(function($q) use ($termo) {
            $q->where('nome', 'like', "%{$termo}%")
              ->orWhere('slug', 'like', "%{$termo}%");
        });
    }

    // ========================================
    // ACESSORS
    // ========================================

    /**
     * Retorna a cor com # se não tiver
     */
    public function getCorAttribute($value)
    {
        return str_starts_with($value, '#') ? $value : "#{$value}";
    }

    // ========================================
    // MUTATORS
    // ========================================

    /**
     * Gera slug automaticamente se não fornecido
     */
    public function setNomeAttribute($value)
    {
        $this->attributes['nome'] = $value;
        
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = \Str::slug($value);
        }
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    /**
     * Retorna quantidade de exercícios ativos
     */
    public function getTotalExerciciosAttribute()
    {
        return $this->exercicios()->where('status', 'ativo')->count();
    }

    /**
     * Retorna quantidade de templates populares
     */
    public function getTotalTemplatesPopularesAttribute()
    {
        return $this->exerciciosTemplates()->where('popular', true)->where('ativo', true)->count();
    }

    /**
     * Verifica se tem exercícios
     */
    public function hasExercicios(): bool
    {
        return $this->exercicios()->exists();
    }

    /**
     * Verifica se tem templates
     */
    public function hasTemplates(): bool
    {
        return $this->exerciciosTemplates()->where('ativo', true)->exists();
    }
}