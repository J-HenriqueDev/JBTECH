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
        Schema::create('solicitacoes_servicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('canal_atendimento'); // WhatsApp, Ligação, Balcão, Email, etc.
            $table->dateTime('data_solicitacao');
            $table->string('tipo_atendimento'); // Presencial, Remoto
            $table->text('descricao');
            $table->text('pendencias')->nullable();
            $table->string('status')->default('pendente'); // pendente, em_andamento, concluido, cancelado
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_servicos');
    }
};
