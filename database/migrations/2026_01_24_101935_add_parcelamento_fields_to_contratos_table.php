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
        Schema::table('contratos', function (Blueprint $table) {
            $table->string('tipo')->default('recorrente')->after('ativo'); // recorrente, parcelado
            $table->integer('qtd_parcelas')->nullable()->after('tipo');
            $table->integer('parcela_atual')->default(1)->after('qtd_parcelas');
            $table->string('dias_personalizados')->nullable()->after('parcela_atual'); // ex: "5,20"
            $table->decimal('valor_total', 10, 2)->nullable()->after('valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'qtd_parcelas', 'parcela_atual', 'dias_personalizados', 'valor_total']);
        });
    }
};
