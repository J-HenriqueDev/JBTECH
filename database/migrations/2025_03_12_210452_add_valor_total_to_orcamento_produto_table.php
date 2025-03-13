<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValorTotalToOrcamentoProdutoTable extends Migration
{
    public function up()
    {
        Schema::table('orcamento_produto', function (Blueprint $table) {
            $table->decimal('valor_total', 10, 2)->after('valor_unitario'); // Adiciona a coluna valor_total
        });
    }

    public function down()
    {
        Schema::table('orcamento_produto', function (Blueprint $table) {
            $table->dropColumn('valor_total'); // Remove a coluna valor_total
        });
    }
}
