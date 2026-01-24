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
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->foreignId('contrato_id')->nullable()->after('venda_id')->constrained('contratos')->onDelete('set null');
            $table->unsignedBigInteger('venda_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cobrancas', function (Blueprint $table) {
            $table->dropForeign(['contrato_id']);
            $table->dropColumn('contrato_id');
            // Revert venda_id to not null might be risky if we have nulls, but for rollback it's expected
            // $table->unsignedBigInteger('venda_id')->nullable(false)->change(); 
        });
    }
};
