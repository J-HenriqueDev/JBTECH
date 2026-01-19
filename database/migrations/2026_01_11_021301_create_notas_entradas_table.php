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
        Schema::create('notas_entradas', function (Blueprint $table) {
            $table->id();
            $table->string('chave_acesso', 44)->unique();
            $table->string('numero_nfe')->nullable();
            $table->string('serie')->nullable();
            $table->string('emitente_cnpj', 14)->nullable();
            $table->string('emitente_nome')->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->timestamp('data_emissao')->nullable();
            $table->string('status')->default('pendente'); // pendente, processada, cancelada
            $table->text('xml_content')->nullable(); // XML completo
            $table->string('manifestacao')->default('sem_manifestacao'); // ciencia, confirmada, desconhecida, nao_realizada
            $table->unsignedBigInteger('user_id')->nullable(); // Quem importou/processou
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_entradas');
    }
};
