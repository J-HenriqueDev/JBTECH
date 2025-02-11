<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->string('codigo_barras', 13)->unique()->nullable();
            $table->string('ncm')->nullable();
            $table->unsignedInteger('estoque')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->foreignId('categoria_id')->constrained()->onDelete('cascade');
            $table->string('fornecedor_cnpj')->nullable();
            $table->string('fornecedor_nome')->nullable();
            $table->string('fornecedor_telefone')->nullable();
            $table->string('fornecedor_email')->nullable();
            $table->timestamps();
        });

        // Insere o produto "Serviço" diretamente na tabela ao criar a migration
        DB::table('produtos')->insert([
            'nome' => 'Serviço',
            'preco_custo' => 0.00,
            'preco_venda' => 0.00,
            'codigo_barras' => '0000000000000',
            'ncm' => '00',
            'estoque' => 0,
            'usuario_id' => 1, // Altere conforme necessário
            'categoria_id' => 6, // Altere conforme necessário
            'fornecedor_cnpj' => '00000000000000',
            'fornecedor_nome' => 'Fornecedor Serviço',
            'fornecedor_telefone' => '00000000000',
            'fornecedor_email' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
