<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrcamentosTable extends Migration
{
    public function up()
    {
      Schema::create('orcamentos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
        $table->date('data');
        $table->date('validade');
        $table->decimal('valor_total', 10, 2)->default(0);
        $table->string('status')->default('pendente'); // Coluna status sem "after"
        $table->text('observacoes')->nullable();
        $table->timestamps();
        });

        Schema::create('orcamento_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orcamento_id')->constrained()->onDelete('cascade');
            $table->foreignId('produto_id')->constrained()->onDelete('cascade');
            $table->integer('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orcamento_produto');
        Schema::dropIfExists('orcamentos');
    }
}
