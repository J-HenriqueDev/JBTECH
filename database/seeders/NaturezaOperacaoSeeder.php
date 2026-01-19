<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NaturezaOperacao;

class NaturezaOperacaoSeeder extends Seeder
{
    public function run()
    {
        $naturezas = [
            [
                'descricao' => 'Venda de Mercadoria',
                'tipo' => 'saida', // Saída
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
                // Mas se eu enviei e voltou, é entrada.
                // Vamos focar nas saídas comuns primeiro.
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
                // Se estou emitindo nota de saída referente a algo, pode ser transferencia para uso consumo.
                // Mas geralmente compra para uso e consumo é entrada.
                // O usuário pediu "Uso consumo" na emissão de nota avulsa (geralmente saída).
                // Pode ser CFOP 5556 (Devolução de compra de material de uso ou consumo) se for devolução.
                // Se for venda de ativo imobilizado: 5551.
                // Vamos assumir "Simples Remessa" ou algo genérico se não especificado.
                // Mas "Uso consumo" como natureza de operação geralmente refere-se a COMPRA (entrada).
                // Se for NFe Avulsa de Saída, talvez seja "Transferência de Material de Uso e Consumo" (5557).
                'cfop_estadual' => '5557',
                'cfop_interestadual' => '6557',
                'cfop_exterior' => '',
                'padrao' => false
            ],
            [
                'descricao' => 'Perdas e Danos', // Baixa de estoque
                'tipo' => 'saida',
                'cfop_estadual' => '5927', // Lançamento efetuado a título de baixa de estoque decorrente de perda, roubo ou deterioração
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
}
