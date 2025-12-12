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
        Schema::table('os', function (Blueprint $table) {
            $table->string('status')->default('pendente')->after('usuario_id');
            $table->text('observacoes')->nullable()->after('avarias');
            $table->decimal('valor_servico', 10, 2)->nullable()->after('observacoes');
            $table->date('data_conclusao')->nullable()->after('prazo_entrega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('os', function (Blueprint $table) {
            $table->dropColumn(['status', 'observacoes', 'valor_servico', 'data_conclusao']);
        });
    }
};
