<?php

namespace Database\Seeders;

use App\Models\Orcamento;
use App\Models\Produto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrcamentosTableSeeder extends Seeder
{
    public function run()
    {
        // Desativa as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpa a tabela de orçamentos
        DB::table('orcamentos')->truncate();

        // Reativa as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Cria 10 orçamentos fictícios
        Orcamento::factory()->count(30)->create()->each(function ($orcamento) {
            // Adiciona produtos ao orçamento
            $produtos = Produto::inRandomOrder()->limit(rand(1, 5))->get();
            $valorTotal = 0;

            foreach ($produtos as $produto) {
                $quantidade = rand(1, 10);
                $valorUnitario = $produto->preco_venda;
                $valorTotalProduto = $quantidade * $valorUnitario;

                // Associa o produto ao orçamento
                $orcamento->produtos()->attach($produto->id, [
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'valor_total' => $valorTotalProduto, // Insere o valor total na tabela pivô
                ]);

                // Soma ao valor total do orçamento
                $valorTotal += $valorTotalProduto;
            }

            // Atualiza o valor total do orçamento
            $orcamento->update(['valor_total' => $valorTotal]);
        });
    }
}
