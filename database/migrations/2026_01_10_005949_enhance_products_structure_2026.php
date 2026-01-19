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
        // 1. Criação da tabela de Fornecedores
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj')->unique()->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('cep')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf')->nullable();
            $table->timestamps();
        });

        // 2. Tabela Pivot Produto <-> Fornecedor (Muitos para Muitos)
        Schema::create('produto_fornecedor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('cascade');
            $table->string('codigo_produto_fornecedor')->nullable(); // Código do produto no fornecedor
            $table->decimal('preco_custo', 10, 2)->nullable(); // Custo específico deste fornecedor
            $table->timestamps();
        });

        // 3. Adição de campos de preços e promoção na tabela Produtos
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('preco_atacado', 10, 2)->nullable()->after('preco_venda');
            $table->integer('qtd_min_atacado')->nullable()->after('preco_atacado');
            $table->decimal('preco_promocional', 10, 2)->nullable()->after('qtd_min_atacado');
            $table->dateTime('inicio_promocao')->nullable()->after('preco_promocional');
            $table->dateTime('fim_promocao')->nullable()->after('inicio_promocao');
        });

        // 4. Adição de palavras-chave para categorização automática
        Schema::table('categorias', function (Blueprint $table) {
            $table->text('palavras_chave')->nullable()->after('nome'); // Ex: "celular,smartphone,iphone"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('palavras_chave');
        });

        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn([
                'preco_atacado',
                'qtd_min_atacado',
                'preco_promocional',
                'inicio_promocao',
                'fim_promocao'
            ]);
        });

        Schema::dropIfExists('produto_fornecedor');
        Schema::dropIfExists('fornecedores');
    }
};
