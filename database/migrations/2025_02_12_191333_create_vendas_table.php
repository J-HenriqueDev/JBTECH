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
    Schema::create('vendas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // ID do usuário que fez a venda
        $table->date('data_venda');
        $table->text('observacoes')->nullable();
        $table->string('status')->default('pendente'); // Coluna status com valor padrão
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('vendas');
}
};
