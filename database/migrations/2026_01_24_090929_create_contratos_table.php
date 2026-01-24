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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->integer('dia_vencimento');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->string('frequencia')->default('mensal'); // mensal, trimestral, semestral, anual
            $table->date('ultimo_faturamento')->nullable();
            $table->date('proximo_faturamento')->nullable();
            
            // Dados fiscais para NFSe automática
            $table->string('codigo_servico')->nullable(); // Código do serviço (LC 116)
            $table->decimal('aliquota_iss', 5, 2)->default(0);
            $table->boolean('iss_retido')->default(false);
            $table->text('discriminacao_servico')->nullable(); // Texto que vai na nota

            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
