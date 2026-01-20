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
        Schema::table('compras', function (Blueprint $table) {
            $table->string('tipo')->default('reposicao')->after('status'); // reposicao, inovacao, uso_interno
            $table->string('prioridade')->default('media')->after('tipo'); // baixa, media, alta
        });

        Schema::table('compra_items', function (Blueprint $table) {
            $table->string('descricao_livre')->nullable()->after('produto_id');
            $table->decimal('valor_unitario', 10, 2)->nullable()->change();
            $table->decimal('valor_total', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compra_items', function (Blueprint $table) {
            $table->decimal('valor_unitario', 10, 2)->nullable(false)->change();
            $table->decimal('valor_total', 10, 2)->nullable(false)->change();
            $table->dropColumn('descricao_livre');
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'prioridade']);
        });
    }
};
