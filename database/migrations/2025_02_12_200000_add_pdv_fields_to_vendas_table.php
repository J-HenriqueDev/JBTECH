<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->foreignId('caixa_id')->nullable()->after('user_id')->constrained('caixas')->onDelete('set null');
            $table->enum('forma_pagamento', ['dinheiro', 'cartao_debito', 'cartao_credito', 'pix', 'outro'])->default('dinheiro')->after('status');
            $table->decimal('valor_recebido', 10, 2)->nullable()->after('valor_total');
            $table->decimal('troco', 10, 2)->nullable()->after('valor_recebido');
            $table->string('numero_cupom')->nullable()->after('troco');
            $table->boolean('sincronizado')->default(false)->after('numero_cupom');
            $table->timestamp('data_sincronizacao')->nullable()->after('sincronizado');
        });
    }

    public function down()
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropForeign(['caixa_id']);
            $table->dropColumn(['caixa_id', 'forma_pagamento', 'valor_recebido', 'troco', 'numero_cupom', 'sincronizado', 'data_sincronizacao']);
        });
    }
};


