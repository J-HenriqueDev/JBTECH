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
        Schema::table('contratos', function (Blueprint $table) {
            $table->foreignId('servico_id')->nullable()->after('cliente_id')->constrained('servicos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropForeign(['servico_id']);
            $table->dropColumn('servico_id');
        });
    }
};
