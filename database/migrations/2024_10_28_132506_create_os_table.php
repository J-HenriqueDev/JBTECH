<?php

// database/migrations/xxxx_xx_xx_create_os_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOSTable extends Migration
{
    public function up()
    {
        Schema::create('os', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->string('tipo_id');
            $table->date('data_de_entrada');
            $table->date('prazo_entrega');
            $table->text('problema_item');
            $table->string('acessorios')->nullable();
            $table->string('senha_do_dispositivo')->nullable();
            $table->string('modelo_do_dispositivo')->nullable(); // Novo campo
            $table->string('sn')->nullable(); // Novo campo
            $table->text('avarias')->nullable();
            $table->json('fotos')->nullable(); // Para armazenar uma lista de fotos
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            // Define a chave estrangeira para cliente_id e usuario_id
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('os');
    }
}
