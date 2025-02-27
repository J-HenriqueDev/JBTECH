<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Correção na importação

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Primeiro, atualize os valores NULL existentes para 0
        DB::table('produtos')->whereNull('estoque')->update(['estoque' => 0]);

        // Em seguida, altere a coluna para NOT NULL com um valor padrão
        Schema::table('produtos', function (Blueprint $table) {
            $table->integer('estoque')->default(0)->nullable(false)->change();
        });
    }

    public function down()
    {
        // Reverta a coluna para nullable, se necessário
        Schema::table('produtos', function (Blueprint $table) {
            $table->unsignedInteger('estoque')->default(0)->nullable()->change();
        });
    }
};
