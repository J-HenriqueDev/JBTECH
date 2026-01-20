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
            $table->string('metodo_pagamento')->nullable()->after('data_pagamento');
            $table->decimal('valor_pago', 10, 2)->nullable()->after('valor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->dropColumn(['metodo_pagamento', 'valor_pago']);
        });
    }
};
