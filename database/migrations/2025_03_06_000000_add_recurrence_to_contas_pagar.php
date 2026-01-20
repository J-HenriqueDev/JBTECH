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
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->boolean('recorrente')->default(false);
            $table->string('frequencia')->nullable()->comment('mensal, semanal, anual'); // mensal, semanal, anual
            $table->integer('dia_vencimento')->nullable(); // Para recorrência mensal
            $table->date('proximo_vencimento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropColumn(['recorrente', 'frequencia', 'dia_vencimento', 'proximo_vencimento']);
        });
    }
};
