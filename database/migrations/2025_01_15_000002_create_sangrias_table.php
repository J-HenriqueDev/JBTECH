<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sangrias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixa_id')->constrained('caixas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('valor', 10, 2);
            $table->text('observacoes')->nullable();
            $table->timestamp('data_hora');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sangrias');
    }
};


