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
        Schema::table('compra_items', function (Blueprint $table) {
            $table->string('status')->default('pendente')->after('valor_total'); // pendente, aprovado, recusado
        });

        Schema::table('compras', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->after('fornecedor_id')->constrained('clientes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });

        Schema::table('compra_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
