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
            $table->string('cest');
            $table->string('tipo_produto');
            $table->unsignedInteger('estoque');
            $table->integer('usuario_id'); // Campo apenas para armazenar o ID do usuário
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade'); // Chave estrangeira para categorias
            $table->string('fornecedor_cnpj')->unique();
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
