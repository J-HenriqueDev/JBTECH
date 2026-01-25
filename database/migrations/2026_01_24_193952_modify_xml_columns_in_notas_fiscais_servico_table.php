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
            $table->longText('xml_envio')->change();
            $table->longText('xml_retorno')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_fiscais_servico', function (Blueprint $table) {
            $table->text('xml_envio')->change();
            $table->text('xml_retorno')->change();
        });
    }
};
