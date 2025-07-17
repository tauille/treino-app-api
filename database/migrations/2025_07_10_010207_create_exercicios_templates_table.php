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
        Schema::create('exercicios_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255); // ex: "Supino Reto"
            $table->foreignId('grupo_muscular_id')->constrained('grupos_musculares')->onDelete('cascade');
            $table->text('descricao')->nullable(); // descrição do exercício
            $table->text('instrucoes')->nullable(); // como executar
            $table->string('imagem_url', 500)->nullable(); // URL da imagem
            $table->boolean('popular')->default(false); // exercícios mais populares
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Índices para performance
            $table->index(['grupo_muscular_id', 'ativo']);
            $table->index(['popular', 'ativo']);
            $table->index('nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercicios_templates');
    }
};