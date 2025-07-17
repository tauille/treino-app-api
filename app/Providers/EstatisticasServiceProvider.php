<?php

namespace App\Providers;

use App\Services\EstatisticasService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class EstatisticasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar o EstatisticasService como singleton
        $this->app->singleton(EstatisticasService::class, function (Application $app) {
            return new EstatisticasService();
        });

        // Criar alias para facilitar uso
        $this->app->alias(EstatisticasService::class, 'estatisticas.service');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Registrar comandos artisan se necessário
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        // Registrar observers ou listeners se necessário
        $this->registerObservers();

        // Configurar cache para estatísticas se necessário
        $this->configurarCache();
    }

    /**
     * Registrar comandos artisan relacionados a estatísticas
     */
    private function registerCommands(): void
    {
        $this->commands([
            // Aqui você pode registrar comandos como:
            // \App\Console\Commands\GerarRelatorioEstatisticas::class,
            // \App\Console\Commands\LimparCacheEstatisticas::class,
        ]);
    }

    /**
     * Registrar observers para atualizar estatísticas automaticamente
     */
    private function registerObservers(): void
    {
        // Observers podem ser registrados aqui para invalidar cache
        // quando dados relevantes forem alterados
        
        // Exemplo:
        // \App\Models\ExecucaoTreino::observe(\App\Observers\EstatisticasObserver::class);
        // \App\Models\ExecucaoExercicio::observe(\App\Observers\EstatisticasObserver::class);
    }

    /**
     * Configurar cache para estatísticas
     */
    private function configurarCache(): void
    {
        // Configurações de cache podem ser definidas aqui
        // Por exemplo, definir tags de cache padrão para estatísticas
        
        if (config('cache.default') === 'redis') {
            // Configurações específicas para Redis
            config(['cache.stores.estatisticas' => [
                'driver' => 'redis',
                'connection' => 'cache',
                'prefix' => 'stats:',
            ]]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            EstatisticasService::class,
            'estatisticas.service',
        ];
    }
}