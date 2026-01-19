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
        Schema::table('notas_fiscais', function (Blueprint $table) {
            $table->string('natureza_operacao')->default('VENDA DE MERCADORIA')->after('serie');
            $table->integer('tipo_documento')->default(1)->comment('0=Entrada, 1=Saída')->after('natureza_operacao');
            $table->integer('finalidade')->default(1)->comment('1=Normal, 2=Complementar, 3=Ajuste, 4=Devolução')->after('tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_fiscais', function (Blueprint $table) {
            $table->dropColumn(['natureza_operacao', 'tipo_documento', 'finalidade']);
        });
    }
};
