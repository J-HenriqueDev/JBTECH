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
        Schema::table('natureza_operacaos', function (Blueprint $table) {
            $table->boolean('calcula_custo')->default(false)->after('padrao');
            $table->boolean('movimenta_estoque')->default(true)->after('calcula_custo');
            $table->boolean('gera_financeiro')->default(true)->after('movimenta_estoque');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('natureza_operacaos', function (Blueprint $table) {
            $table->dropColumn(['calcula_custo', 'movimenta_estoque', 'gera_financeiro']);
        });
    }
};
