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
            if (!Schema::hasColumn('contas_pagar', 'recorrente')) {
                $table->boolean('recorrente')->default(false);
            }
            if (!Schema::hasColumn('contas_pagar', 'frequencia')) {
                $table->string('frequencia')->nullable()->comment('mensal, semanal, anual');
            }
            if (!Schema::hasColumn('contas_pagar', 'dia_vencimento')) {
                $table->integer('dia_vencimento')->nullable();
            }
            if (!Schema::hasColumn('contas_pagar', 'proximo_vencimento')) {
                $table->date('proximo_vencimento')->nullable();
            }
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
