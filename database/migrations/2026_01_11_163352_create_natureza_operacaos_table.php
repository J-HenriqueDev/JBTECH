<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\NaturezaOperacao;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('natureza_operacaos', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->enum('tipo', ['entrada', 'saida']);
            $table->string('cfop_estadual', 4);
            $table->string('cfop_interestadual', 4);
            $table->string('cfop_exterior', 4)->nullable();
            $table->boolean('padrao')->default(false);
            $table->timestamps();
        });

        $naturezas = [
            [
                'descricao' => 'Venda de Mercadoria',
                'tipo' => 'saida',
                'cfop_estadual' => '5102',
                'cfop_interestadual' => '6102',
                'cfop_exterior' => '7102',
                'padrao' => true
            ],
            [
                'descricao' => 'Revenda de Mercadoria',
                'tipo' => 'saida',
                'cfop_estadual' => '5102',
                'cfop_interestadual' => '6102',
                'cfop_exterior' => '7102',
                'padrao' => false
            ],
            [
                'descricao' => 'Venda de Produção do Estabelecimento',
                'tipo' => 'saida',
                'cfop_estadual' => '5101',
                'cfop_interestadual' => '6101',
                'cfop_exterior' => '7101',
                'padrao' => false
            ],
            [
                'descricao' => 'Devolução de Compra para Comercialização',
                'tipo' => 'saida',
                'cfop_estadual' => '5202',
                'cfop_interestadual' => '6202',
                'cfop_exterior' => '7202',
                'padrao' => false
            ],
            [
                'descricao' => 'Remessa para Conserto',
                'tipo' => 'saida',
                'cfop_estadual' => '5915',
                'cfop_interestadual' => '6915',
                'cfop_exterior' => '7915',
                'padrao' => false
            ],
            [
                'descricao' => 'Retorno de Conserto',
                'tipo' => 'entrada',
                'cfop_estadual' => '5916',
                'cfop_interestadual' => '6916',
                'cfop_exterior' => '',
                'padrao' => false
            ],
            [
                'descricao' => 'Bonificação, Doação ou Brinde',
                'tipo' => 'saida',
                'cfop_estadual' => '5910',
                'cfop_interestadual' => '6910',
                'cfop_exterior' => '',
                'padrao' => false
            ],
            [
                'descricao' => 'Uso e Consumo',
                'tipo' => 'saida',
                'cfop_estadual' => '5557',
                'cfop_interestadual' => '6557',
                'cfop_exterior' => '',
                'padrao' => false
            ],
            [
                'descricao' => 'Perdas e Danos',
                'tipo' => 'saida',
                'cfop_estadual' => '5927',
                'cfop_interestadual' => '',
                'cfop_exterior' => '',
                'padrao' => false
            ]
        ];

        foreach ($naturezas as $nat) {
            NaturezaOperacao::firstOrCreate(
                ['descricao' => $nat['descricao']],
                $nat
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('natureza_operacaos');
    }
};
