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
        Schema::create('grupos_musculares', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100); // ex: "Peito"
            $table->string('slug', 100)->unique(); // ex: "peito"
            $table->string('icone', 10); // ex: "💪"
            $table->string('cor', 7); // hex color: "#FF6B6B"
            $table->text('descricao')->nullable();
            $table->integer('ordem')->default(0); // para ordenação na lista
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Índices para performance
            $table->index(['ativo', 'ordem']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos_musculares');
    }
};