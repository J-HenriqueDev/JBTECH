<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutosTable extends Migration
{
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->decimal('preco_custo', 10, 2);
            $table->decimal('preco_venda', 10, 2);
            $table->string('codigo_barras', 13)->unique();
            $table->string('ncm');
            // Removido 'cest' e 'tipo_produto' se não forem utilizados
            $table->unsignedInteger('estoque');
            // $table->foreignId('usuario_id')->constrained()->onDelete('cascade'); // Chave estrangeira para usuários
            $table->unsignedBigInteger('usuario_id');
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade'); // Chave estrangeira para categorias
            $table->string('fornecedor_cnpj');
            $table->string('fornecedor_nome');
            $table->string('fornecedor_telefone');
            $table->string('fornecedor_email')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
