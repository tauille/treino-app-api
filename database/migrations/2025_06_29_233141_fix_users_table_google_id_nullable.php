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
        Schema::table('users', function (Blueprint $table) {
            // Tornar google_id nullable
            $table->string('google_id')->nullable()->change();
            
            // Tornar outros campos opcionais também
            $table->timestamp('trial_started_at')->nullable()->change();
            $table->boolean('is_premium')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverter mudanças (se necessário)
            $table->string('google_id')->nullable(false)->change();
        });
    }
};