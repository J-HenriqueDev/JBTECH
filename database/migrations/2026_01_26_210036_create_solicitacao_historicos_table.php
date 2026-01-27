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
        Schema::create('solicitacao_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitacao_id')->constrained('solicitacoes_servicos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('acao'); // Ex: Criou, Editou, Alterou Status
            $table->string('status_anterior')->nullable();
            $table->string('status_novo')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacao_historicos');
    }
};
