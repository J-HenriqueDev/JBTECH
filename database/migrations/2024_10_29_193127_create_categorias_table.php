<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateCategoriasTable extends Migration
{
    public function up()
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Nome da categoria
            $table->timestamps();
        });

        // Inserir categorias básicas
        DB::table('categorias')->insert([
            ['nome' => 'Placas-mãe', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Memórias', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'SSD', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'HD', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Computador', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Outros', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('categorias');
    }
}
