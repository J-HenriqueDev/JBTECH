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
        // 1. Adicionar campos extras na tabela produtos
        Schema::table('produtos', function (Blueprint $table) {
            // Status e Tipo
            $table->boolean('ativo')->default(true)->after('nome'); // Campo geral de ativo/inativo
            $table->string('tipo_item')->default('00')->comment('00: Revenda, 01: Matéria-prima, etc')->after('ativo');

            // Estoque detalhado
            $table->integer('estoque_minimo')->default(0)->after('estoque');
            $table->integer('estoque_maximo')->nullable()->after('estoque_minimo');
            $table->string('localizacao')->nullable()->comment('Prateleira, Corredor, etc')->after('estoque_maximo');

            // Dimensões e Peso (Outros)
            $table->decimal('peso_liquido', 10, 3)->nullable()->after('localizacao');
            $table->decimal('peso_bruto', 10, 3)->nullable()->after('peso_liquido');
            $table->decimal('largura', 10, 2)->nullable()->after('peso_bruto');
            $table->decimal('altura', 10, 2)->nullable()->after('largura');
            $table->decimal('comprimento', 10, 2)->nullable()->after('altura');

            // Observações
            $table->text('observacoes_internas')->nullable()->after('comprimento');
        });

        // 2. Tabela de códigos de barras adicionais
        Schema::create('produto_codigos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->string('codigo', 14); // Até 14 chars (GTIN-14)
            $table->string('descricao')->nullable()->comment('Ex: Caixa com 12, Unidade, etc');
            $table->timestamps();
        });

        // 3. Tabela pivô Produto <-> Fornecedor
        // Verifica se já existe para evitar erro (caso tenha sido criada manualmente antes)
        if (!Schema::hasTable('produto_fornecedor')) {
            Schema::create('produto_fornecedor', function (Blueprint $table) {
                $table->id();
                $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
                $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade');
                $table->string('codigo_produto_fornecedor')->nullable(); // Código que o fornecedor usa
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produto_codigos');
        Schema::dropIfExists('produto_fornecedor');

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn([
                'ativo',
                'tipo_item',
                'estoque_minimo',
                'estoque_maximo',
                'localizacao',
                'peso_liquido',
                'peso_bruto',
                'largura',
                'altura',
                'comprimento',
                'observacoes_internas'
            ]);
        });
    }
};
