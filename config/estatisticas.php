<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configurações Gerais de Estatísticas
    |--------------------------------------------------------------------------
    |
    | Aqui você pode configurar comportamentos padrão do sistema de estatísticas,
    | como cache, metas padrão, limites de consulta e outras preferências.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache de Estatísticas
    |--------------------------------------------------------------------------
    */
    'cache' => [
        // Habilitar cache de estatísticas
        'enabled' => env('ESTATISTICAS_CACHE_ENABLED', true),
        
        // Driver de cache a ser usado (null = usar padrão do app)
        'driver' => env('ESTATISTICAS_CACHE_DRIVER', null),
        
        // Tempo padrão de cache em minutos
        'ttl' => env('ESTATISTICAS_CACHE_TTL', 60),
        
        // Prefixo para chaves de cache
        'prefix' => env('ESTATISTICAS_CACHE_PREFIX', 'stats'),
        
        // Tags de cache para invalidação seletiva
        'tags' => [
            'dashboard' => 'stats:dashboard',
            'progresso' => 'stats:progresso',
            'rankings' => 'stats:rankings',
            'evolucao' => 'stats:evolucao',
            'grupos' => 'stats:grupos',
        ],
        
        // Tempos específicos de cache por tipo (em minutos)
        'ttl_por_tipo' => [
            'dashboard' => 30,      // Dashboard atualizado a cada 30 min
            'progresso' => 60,      // Progresso a cada 1 hora
            'rankings' => 120,      // Rankings a cada 2 horas
            'evolucao' => 1440,     // Evolução a cada 24 horas
            'grupos' => 240,        // Grupos musculares a cada 4 horas
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metas Padrão do Usuário
    |--------------------------------------------------------------------------
    */
    'metas_padrao' => [
        // Treinos por semana
        'treinos_semanais' => env('META_TREINOS_SEMANAIS', 3),
        
        // Treinos por mês
        'treinos_mensais' => env('META_TREINOS_MENSAIS', 12),
        
        // Sequência de dias consecutivos
        'sequencia_dias' => env('META_SEQUENCIA_DIAS', 7),
        
        // Tempo total semanal em minutos
        'tempo_semanal_minutos' => env('META_TEMPO_SEMANAL', 180),
        
        // Tempo total mensal em minutos
        'tempo_mensal_minutos' => env('META_TEMPO_MENSAL', 720),
        
        // Exercícios diferentes por semana
        'exercicios_semanais' => env('META_EXERCICIOS_SEMANAIS', 10),
        
        // Grupos musculares por semana
        'grupos_semanais' => env('META_GRUPOS_SEMANAIS', 4),
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites de Consulta
    |--------------------------------------------------------------------------
    */
    'limites' => [
        // Máximo de registros em rankings
        'max_ranking' => env('ESTATISTICAS_MAX_RANKING', 50),
        
        // Máximo de dias para análise de progresso
        'max_dias_progresso' => env('ESTATISTICAS_MAX_DIAS', 365),
        
        // Máximo de exercícios em evolução
        'max_evolucao_exercicios' => env('ESTATISTICAS_MAX_EVOLUCAO', 100),
        
        // Máximo de registros para exportação
        'max_exportacao' => env('ESTATISTICAS_MAX_EXPORT', 1000),
        
        // Limite de consultas por minuto por usuário
        'rate_limit' => env('ESTATISTICAS_RATE_LIMIT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Formatação e Exibição
    |--------------------------------------------------------------------------
    */
    'formato' => [
        // Formato de data padrão
        'data' => env('ESTATISTICAS_FORMATO_DATA', 'd/m/Y'),
        
        // Formato de data e hora
        'data_hora' => env('ESTATISTICAS_FORMATO_DATA_HORA', 'd/m/Y H:i'),
        
        // Formato de tempo (duração)
        'tempo' => env('ESTATISTICAS_FORMATO_TEMPO', 'H:i:s'),
        
        // Precisão decimal para pesos
        'peso_decimais' => env('ESTATISTICAS_PESO_DECIMAIS', 1),
        
        // Precisão decimal para percentuais
        'percentual_decimais' => env('ESTATISTICAS_PERCENTUAL_DECIMAIS', 1),
        
        // Moeda padrão (para futuras funcionalidades premium)
        'moeda' => env('ESTATISTICAS_MOEDA', 'BRL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Períodos Padrão para Análises
    |--------------------------------------------------------------------------
    */
    'periodos' => [
        // Período padrão para dashboard (em dias)
        'dashboard' => env('PERIODO_DASHBOARD', 30),
        
        // Período padrão para progresso (em dias)
        'progresso' => env('PERIODO_PROGRESSO', 30),
        
        // Período padrão para comparativos (em dias)
        'comparativo' => env('PERIODO_COMPARATIVO', 30),
        
        // Período padrão para rankings (em dias)
        'rankings' => env('PERIODO_RANKINGS', 365),
        
        // Período para cálculo de consistência (em dias)
        'consistencia' => env('PERIODO_CONSISTENCIA', 90),
        
        // Períodos disponíveis para seleção
        'opcoes' => [7, 15, 30, 60, 90, 180, 365],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Performance
    |--------------------------------------------------------------------------
    */
    'performance' => [
        // Usar índices de banco otimizados
        'indices_otimizados' => env('ESTATISTICAS_INDICES_OTIMIZADOS', true),
        
        // Usar consultas em lote
        'batch_queries' => env('ESTATISTICAS_BATCH_QUERIES', true),
        
        // Tamanho do lote para consultas
        'batch_size' => env('ESTATISTICAS_BATCH_SIZE', 100),
        
        // Usar lazy loading para grandes datasets
        'lazy_loading' => env('ESTATISTICAS_LAZY_LOADING', true),
        
        // Timeout para consultas longas (em segundos)
        'query_timeout' => env('ESTATISTICAS_QUERY_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notificações e Alertas
    |--------------------------------------------------------------------------
    */
    'notificacoes' => [
        // Habilitar notificações de marcos
        'marcos_enabled' => env('NOTIF_MARCOS_ENABLED', true),
        
        // Marcos para notificação (treinos executados)
        'marcos_treinos' => [10, 25, 50, 100, 250, 500, 1000],
        
        // Marcos para sequências de dias
        'marcos_sequencias' => [7, 15, 30, 60, 100, 365],
        
        // Notificar recordes pessoais
        'recordes_enabled' => env('NOTIF_RECORDES_ENABLED', true),
        
        // Notificar metas atingidas
        'metas_enabled' => env('NOTIF_METAS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Exportação de Dados
    |--------------------------------------------------------------------------
    */
    'exportacao' => [
        // Formatos suportados
        'formatos' => ['json', 'csv', 'pdf', 'excel'],
        
        // Formato padrão
        'formato_padrao' => env('EXPORT_FORMATO_PADRAO', 'json'),
        
        // Incluir gráficos nas exportações
        'incluir_graficos' => env('EXPORT_INCLUIR_GRAFICOS', false),
        
        // Compactar arquivos grandes
        'compactar' => env('EXPORT_COMPACTAR', true),
        
        // Tamanho máximo do arquivo em MB
        'tamanho_max_mb' => env('EXPORT_TAMANHO_MAX', 50),
        
        // Tempo de vida dos arquivos exportados (em horas)
        'ttl_horas' => env('EXPORT_TTL_HORAS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações Experimentais
    |--------------------------------------------------------------------------
    */
    'experimental' => [
        // Machine Learning para previsões
        'ml_predicoes' => env('EXPERIMENTAL_ML_PREDICOES', false),
        
        // Análises avançadas de padrões
        'analise_padroes' => env('EXPERIMENTAL_ANALISE_PADROES', false),
        
        // Comparações com outros usuários (anonimizadas)
        'comparacoes_anonimas' => env('EXPERIMENTAL_COMPARACOES', false),
        
        // Sugestões inteligentes de treinos
        'sugestoes_ia' => env('EXPERIMENTAL_SUGESTOES_IA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integração com APIs Externas
    |--------------------------------------------------------------------------
    */
    'integracoes' => [
        // Google Fit
        'google_fit' => [
            'enabled' => env('INTEGRACAO_GOOGLE_FIT', false),
            'client_id' => env('GOOGLE_FIT_CLIENT_ID'),
            'client_secret' => env('GOOGLE_FIT_CLIENT_SECRET'),
        ],
        
        // Apple Health
        'apple_health' => [
            'enabled' => env('INTEGRACAO_APPLE_HEALTH', false),
        ],
        
        // Strava
        'strava' => [
            'enabled' => env('INTEGRACAO_STRAVA', false),
            'client_id' => env('STRAVA_CLIENT_ID'),
            'client_secret' => env('STRAVA_CLIENT_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Desenvolvimento
    |--------------------------------------------------------------------------
    */
    'debug' => [
        // Log de queries de estatísticas
        'log_queries' => env('ESTATISTICAS_LOG_QUERIES', false),
        
        // Mostrar tempo de execução
        'show_timing' => env('ESTATISTICAS_SHOW_TIMING', false),
        
        // Modo de desenvolvimento verbose
        'verbose' => env('ESTATISTICAS_VERBOSE', false),
        
        // Faker para dados de teste
        'fake_data' => env('ESTATISTICAS_FAKE_DATA', false),
    ],

];