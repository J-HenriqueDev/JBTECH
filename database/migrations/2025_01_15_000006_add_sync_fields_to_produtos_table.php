<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->boolean('sincronizado')->default(true)->after('estoque');
            $table->timestamp('ultima_sincronizacao')->nullable()->after('sincronizado');
            $table->boolean('ativo_pdv')->default(true)->after('ultima_sincronizacao');
        });
    }

    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['sincronizado', 'ultima_sincronizacao', 'ativo_pdv']);
        });
    }
};


