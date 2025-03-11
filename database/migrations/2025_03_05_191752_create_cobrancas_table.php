<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('cobrancas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
        $table->string('metodo_pagamento'); // PIX, boleto, cartao_credito, link_pagamento
        $table->string('status')->default('pendente'); // pendente, pago, cancelado
        $table->decimal('valor', 10, 2);
        $table->date('data_vencimento')->nullable(); // Para boletos
        $table->string('codigo_pix')->nullable(); // Código PIX
        $table->string('link_boleto')->nullable(); // Link do boleto
        $table->string('link_pagamento')->nullable(); // Link de pagamento (para cartão de crédito online)
        $table->boolean('recorrente')->default(false); // Indica se a cobrança é recorrente
        $table->string('frequencia_recorrencia')->nullable(); // Mensal, trimestral, anual
        $table->date('proxima_cobranca')->nullable(); // Data da próxima cobrança (para pagamentos recorrentes)
        $table->boolean('enviar_email')->default(false); // Se a cobrança foi enviada por e-mail
        $table->boolean('enviar_whatsapp')->default(false); // Se a cobrança foi enviada por WhatsApp
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
    }
};
