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
        Schema::table('natureza_operacaos', function (Blueprint $table) {
            $table->integer('finNFe')->default(1)->after('gera_financeiro')->comment('Finalidade de emissão da NF-e (1=Normal, 2=Complementar, 3=Ajuste, 4=Devolução)');
            $table->integer('indPres')->default(1)->after('finNFe')->comment('Indicador de presença do comprador (0=Não se aplica, 1=Presencial, 2=Internet, 3=Teleatendimento, 4=NFC-e a domicílio, 9=Outros)');
            $table->boolean('consumidor_final')->default(false)->after('indPres')->comment('Indica operação com Consumidor Final (0=Não, 1=Sim)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('natureza_operacaos', function (Blueprint $table) {
            $table->dropColumn(['finNFe', 'indPres', 'consumidor_final']);
        });
    }
};
