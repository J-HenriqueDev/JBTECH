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
        Schema::create('notas_fiscais_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('numero_rps')->nullable(); // Recibo Provisório de Serviços
            $table->string('serie_rps')->nullable();
            $table->string('numero_nfse')->nullable(); // Número retornado pela prefeitura
            $table->string('chave_acesso')->nullable(); // Código de verificação
            $table->dateTime('data_emissao')->nullable();
            $table->decimal('valor_servico', 10, 2);
            $table->decimal('valor_deducoes', 10, 2)->default(0);
            $table->decimal('valor_iss', 10, 2)->default(0);
            $table->decimal('aliquota_iss', 5, 2)->default(0);
            $table->boolean('iss_retido')->default(false);
            $table->decimal('valor_pis', 10, 2)->default(0);
            $table->decimal('valor_cofins', 10, 2)->default(0);
            $table->decimal('valor_inss', 10, 2)->default(0);
            $table->decimal('valor_ir', 10, 2)->default(0);
            $table->decimal('valor_csll', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2); // Valor líquido + impostos retidos? Ou valor do serviço? Geralmente Valor Serviço - Retenções
            $table->text('discriminacao');
            $table->string('codigo_servico')->nullable(); // Item da lista de serviço (LC 116/03)
            $table->string('cnae')->nullable();
            $table->string('municipio_prestacao')->nullable(); // Código IBGE
            $table->enum('status', ['pendente', 'processando', 'autorizada', 'rejeitada', 'cancelada'])->default('pendente');
            $table->text('xml_envio')->nullable();
            $table->text('xml_retorno')->nullable();
            $table->string('link_nfse')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            $table->foreignId('user_id')->constrained('users'); // Quem emitiu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais_servico');
    }
};
