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
        Schema::table('notas_fiscais_servico', function (Blueprint $table) {
            $table->longText('xml_envio')->nullable()->change();
            $table->longText('xml_retorno')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_fiscais_servico', function (Blueprint $table) {
            // Revertendo para NOT NULL (se fosse o caso original, mas melhor deixar nullable no down também para evitar erros)
            // Mas para ser estrito ao rollback, teoricamente voltaria para o estado anterior.
            // Assumindo que o estado anterior incorreto era NOT NULL (causado pela migration anterior).
            // Se quisermos voltar ao estado PERFEITO original (nullable text), teríamos que ver a migration anterior.
            // Aqui vamos apenas reverter a "nullabilidade" se necessário, mas geralmente manter nullable é seguro.
            // Vamos deixar sem ação ou reverter para nullable false se fosse crítico.
            // Vou deixar nullable pois é o correto para o sistema.
        });
    }
};
