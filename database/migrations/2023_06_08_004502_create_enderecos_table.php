<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->string('cep');
            $table->string('endereco');
            $table->string('numero'); // Certifique-se de que esta linha exista
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
