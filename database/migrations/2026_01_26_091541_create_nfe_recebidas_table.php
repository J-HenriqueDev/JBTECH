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
        Schema::create('nfe_recebidas', function (Blueprint $table) {
            $table->id();
            $table->string('chave', 44)->unique();
            $table->string('nsu');
            $table->enum('status', ['resumo', 'manifestado', 'concluido'])->default('resumo');
            $table->dateTime('data_emissao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_recebidas');
    }
};
