<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exercicios', function (Blueprint $table) {
            // Adicionar novas colunas
            $table->foreignId('grupo_muscular_id')->nullable()->constrained('grupos_musculares')->onDelete('set null');
            $table->foreignId('exercicio_template_id')->nullable()->constrained('exercicios_templates')->onDelete('set null');
            
            // Campos para histórico/progressão
            $table->decimal('peso_anterior', 8, 2)->nullable()->comment('Último peso usado pelo usuário');
            $table->integer('reps_anterior')->nullable()->comment('Últimas repetições feitas');
            
            // Melhorar nomenclatura dos campos de tempo (opcional)
            // Renomear tempo_execucao para tempo_segundos se quiser clareza
            // $table->renameColumn('tempo_execucao', 'tempo_segundos');
            // $table->renameColumn('tempo_descanso', 'descanso_segundos');
            
            // Índices para performance
            $table->index(['grupo_muscular_id', 'status']);
            $table->index('exercicio_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercicios', function (Blueprint $table) {
            // Remover foreign keys
            $table->dropForeign(['grupo_muscular_id']);
            $table->dropForeign(['exercicio_template_id']);
            
            // Remover colunas
            $table->dropColumn([
                'grupo_muscular_id',
                'exercicio_template_id',
                'peso_anterior',
                'reps_anterior'
            ]);
            
            // Remover índices
            $table->dropIndex(['grupo_muscular_id', 'status']);
            $table->dropIndex(['exercicio_template_id']);
        });
    }
};