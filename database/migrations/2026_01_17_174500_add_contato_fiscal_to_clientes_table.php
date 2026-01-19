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
            if (!Schema::hasColumn('clientes', 'telefone_secundario')) {
                $table->string('telefone_secundario')->nullable()->after('telefone');
            }
            if (!Schema::hasColumn('clientes', 'email_secundario')) {
                $table->string('email_secundario')->nullable()->after('email');
            }
            if (!Schema::hasColumn('clientes', 'inscricao_municipal')) {
                $table->string('inscricao_municipal')->nullable()->after('inscricao_estadual');
            }
            if (!Schema::hasColumn('clientes', 'indicador_ie')) {
                $table->integer('indicador_ie')->nullable()->after('inscricao_municipal');
            }
            if (!Schema::hasColumn('clientes', 'suframa')) {
                $table->string('suframa')->nullable()->after('indicador_ie');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'telefone_secundario',
                'email_secundario',
                'inscricao_municipal',
                'indicador_ie',
                'suframa',
            ]);
        });
    }
};
