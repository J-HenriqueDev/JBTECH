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
        Schema::table('solicitacoes_servicos', function (Blueprint $table) {
            $table->unsignedBigInteger('atendente_id')->nullable()->after('status');
            $table->foreign('atendente_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitacoes_servicos', function (Blueprint $table) {
            $table->dropForeign(['atendente_id']);
            $table->dropColumn('atendente_id');
        });
    }
};
