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
        Schema::create('exercicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treino_id')->constrained()->onDelete('cascade');
            $table->string('nome_exercicio');
            $table->enum('tipo_execucao', ['repeticao', 'tempo']);
            $table->integer('repeticoes')->nullable();
            $table->integer('tempo_execucao')->nullable(); // em segundos
            $table->integer('tempo_descanso'); // em segundos
            $table->string('imagem_path')->nullable();
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
            
            $table->index(['treino_id', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercicios');
    }
};