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
        Schema::table('configuracoes', function (Blueprint $table) {
            // Remove o unique da chave para permitir múltiplas configurações por usuário
            $table->dropUnique(['chave']);
            
            // Adiciona user_id (nullable para manter configurações globais)
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            
            // Cria índice composto para chave + user_id
            $table->unique(['chave', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropUnique(['chave', 'user_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->unique('chave');
        });
    }
};
