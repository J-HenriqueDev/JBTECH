<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->integer('estoque')->default(0)->change(); // Remove o UNSIGNED
        });
    }

    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->unsignedInteger('estoque')->default(0)->change(); // Volta ao UNSIGNED
        });
    }
};
