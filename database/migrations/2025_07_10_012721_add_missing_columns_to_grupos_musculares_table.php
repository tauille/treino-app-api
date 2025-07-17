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
        Schema::table('grupos_musculares', function (Blueprint $table) {
            // Adicionar colunas faltantes
            $table->string('nome', 100)->after('id');
            $table->string('slug', 100)->unique()->after('nome');
            $table->string('icone', 10)->after('slug');
            $table->string('cor', 7)->after('icone');
            $table->text('descricao')->nullable()->after('cor');
            $table->integer('ordem')->default(0)->after('descricao');
            $table->boolean('ativo')->default(true)->after('ordem');
            
            // Adicionar índices
            $table->index(['ativo', 'ordem']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos_musculares', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex(['ativo', 'ordem']);
            $table->dropIndex(['slug']);
            
            // Remover colunas
            $table->dropColumn([
                'nome',
                'slug', 
                'icone',
                'cor',
                'descricao',
                'ordem',
                'ativo'
            ]);
        });
    }
};