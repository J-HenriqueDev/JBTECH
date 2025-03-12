<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Usuário que realizou a ação
            $table->string('categoria'); // Categoria da ação (ex: "Orçamento")
            $table->string('acao'); // Ação realizada (ex: "Marcar como Apagado")
            $table->text('detalhes')->nullable(); // Detalhes adicionais (ex: ID do orçamento)
            $table->timestamps(); // Data e hora da ação

            // Chave estrangeira para a tabela de usuários
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
