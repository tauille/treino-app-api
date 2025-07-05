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
        // Adicionar campos que faltam na tabela TREINOS
        Schema::table('treinos', function (Blueprint $table) {
            $table->text('descricao')->nullable()->after('tipo_treino');
            $table->enum('dificuldade', ['iniciante', 'intermediario', 'avancado'])->nullable()->after('descricao');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo')->after('dificuldade');
            
            // Índices para melhor performance
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'dificuldade']);
        });

        // Adicionar campos que faltam na tabela EXERCICIOS
        Schema::table('exercicios', function (Blueprint $table) {
            $table->string('grupo_muscular')->nullable()->after('descricao');
            $table->integer('series')->default(1)->after('grupo_muscular');
            $table->decimal('peso', 5, 2)->nullable()->after('series');
            $table->string('unidade_peso', 10)->default('kg')->after('peso');
            $table->text('observacoes')->nullable()->after('unidade_peso');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo')->after('observacoes');
            
            // Índices para melhor performance
            $table->index(['treino_id', 'ordem']);
            $table->index(['treino_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treinos', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'dificuldade']);
            $table->dropColumn(['descricao', 'dificuldade', 'status']);
        });

        Schema::table('exercicios', function (Blueprint $table) {
            $table->dropIndex(['treino_id', 'ordem']);
            $table->dropIndex(['treino_id', 'status']);
            $table->dropColumn(['grupo_muscular', 'series', 'peso', 'unidade_peso', 'observacoes', 'status']);
        });
    }
};