<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'trial_started_at',
        'is_premium',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'trial_started_at' => 'datetime',
        'is_premium' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relacionamento com treinos
     */
    public function treinos()
    {
        return $this->hasMany(Treino::class);
    }

    /**
     * Verifica se o período de teste está ativo
     */
    public function isTrialActive(): bool
    {
        if (!$this->trial_started_at) {
            return false;
        }
        
        return $this->trial_started_at->addDays(21)->isFuture();
    }

    /**
     * Verifica se o usuário pode usar funcionalidades premium
     */
    public function canUsePremiumFeatures(): bool
    {
        return $this->is_premium || $this->isTrialActive();
    }

    /**
     * Retorna quantos dias restam do período de teste
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->trial_started_at) {
            return 0;
        }

        $trialEnd = $this->trial_started_at->addDays(21);
        $now = Carbon::now();

        if ($trialEnd->isPast()) {
            return 0;
        }

        return $now->diffInDays($trialEnd, false);
    }

    /**
     * Inicia o período de teste
     */
    public function startTrial(): bool
    {
        if ($this->trial_started_at || $this->is_premium) {
            return false;
        }

        $this->trial_started_at = Carbon::now();
        return $this->save();
    }
}