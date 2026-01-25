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
        Schema::table('notas_fiscais_servico', function (Blueprint $table) {
            $table->foreignId('venda_id')->nullable()->after('id')->constrained('vendas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_fiscais_servico', function (Blueprint $table) {
            $table->dropForeign(['venda_id']);
            $table->dropColumn('venda_id');
        });
    }
};
