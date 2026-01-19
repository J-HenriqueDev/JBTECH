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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('email_secundario')->nullable()->after('email');
            $table->string('telefone_secundario')->nullable()->after('telefone');
            $table->string('inscricao_municipal')->nullable()->after('inscricao_estadual');
            $table->integer('indicador_ie')->default(9)->comment('1=Contribuinte, 2=Isento, 9=Não Contribuinte')->after('inscricao_estadual');
            $table->string('suframa')->nullable()->after('inscricao_municipal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['email_secundario', 'telefone_secundario', 'inscricao_municipal', 'indicador_ie', 'suframa']);
        });
    }
};
