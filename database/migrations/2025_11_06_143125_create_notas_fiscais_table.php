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
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('numero_nfe')->unique()->nullable();
            $table->string('chave_acesso', 44)->unique()->nullable();
            $table->string('serie')->default('1');
            $table->enum('status', ['pendente', 'processando', 'autorizada', 'rejeitada', 'cancelada'])->default('pendente');
            $table->text('xml')->nullable();
            $table->text('xml_cancelamento')->nullable();
            $table->text('protocolo')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            $table->decimal('valor_total', 10, 2);
            $table->date('data_emissao')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->text('observacoes')->nullable();
            $table->json('dados_emitente')->nullable(); // Dados do emitente (empresa)
            $table->json('dados_destinatario')->nullable(); // Dados do destinatário
            $table->json('produtos')->nullable(); // Produtos da NF-e em JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
