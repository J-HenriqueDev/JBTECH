<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('caixas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('data_abertura');
            $table->time('hora_abertura');
            $table->date('data_fechamento')->nullable();
            $table->time('hora_fechamento')->nullable();
            $table->decimal('valor_abertura', 10, 2)->default(0);
            $table->decimal('valor_total_vendas', 10, 2)->default(0);
            $table->decimal('valor_total_sangrias', 10, 2)->default(0);
            $table->decimal('valor_total_suprimentos', 10, 2)->default(0);
            $table->decimal('valor_esperado', 10, 2)->default(0);
            $table->decimal('valor_fechamento', 10, 2)->nullable();
            $table->decimal('diferenca', 10, 2)->nullable();
            $table->enum('status', ['aberto', 'fechado'])->default('aberto');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('caixas');
    }
};


