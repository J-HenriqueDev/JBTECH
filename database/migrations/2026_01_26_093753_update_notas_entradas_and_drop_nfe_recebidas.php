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
    // Remove a tabela duplicada criada anteriormente
    Schema::dropIfExists('nfe_recebidas');

    Schema::table('notas_entradas', function (Blueprint $table) {
      // Garante LONGTEXT para armazenar XMLs grandes sem truncar
      $table->longText('xml_content')->nullable()->change();

      // Adiciona NSU para controle de sincronização se não existir
      if (!Schema::hasColumn('notas_entradas', 'nsu')) {
        $table->string('nsu')->nullable()->after('chave_acesso')->index();
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('notas_entradas', function (Blueprint $table) {
      $table->text('xml_content')->nullable()->change();

      if (Schema::hasColumn('notas_entradas', 'nsu')) {
        $table->dropColumn('nsu');
      }
    });

    // Recria a tabela removida (estrutura aproximada da original)
    Schema::create('nfe_recebidas', function (Blueprint $table) {
      $table->id();
      $table->string('chave', 44)->unique();
      $table->string('nsu');
      $table->string('status'); // resumo, manifestado, concluido
      $table->dateTime('data_emissao')->nullable();
      $table->longText('xml_content')->nullable();
      $table->timestamps();
    });
  }
};
