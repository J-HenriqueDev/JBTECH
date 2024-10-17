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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_produto');
            $table->unsignedBigInteger('codigo_barras')->nullable();
            $table->decimal('preco_venda', 8, 2);
            // Adicione a coluna apenas se ainda não existir
            if (!Schema::hasColumn('produtos', 'categoria_id')) {
                $table->unsignedBigInteger('categoria_id');
            }
            $table->string('local_impressao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
