<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venda;
use App\Models\Produto;
use App\Models\Clientes;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VendaSeeder extends Seeder
{
    /**
     * Executa o seeder.
     */
    public function run()
    {
        // Desabilita as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpa as tabelas relacionadas
        DB::table('produto_venda')->truncate();
        Venda::truncate();

        // Reabilita as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Obtém todos os clientes, usuários e produtos disponíveis
        $clientes = Clientes::all();
        $usuarios = User::all();
        $produtos = Produto::all();

        // Cria 50 vendas fictícias
        for ($i = 0; $i < 50; $i++) {
            // Seleciona um cliente e um usuário aleatórios
            $cliente = $clientes->random();
            $usuario = $usuarios->random();

            // Cria a venda
            $venda = Venda::create([
                'cliente_id' => $cliente->id,
                'user_id' => $usuario->id,
                'data_venda' => now()->subDays(rand(1, 30)), // Data aleatória nos últimos 30 dias
                'observacoes' => 'Venda gerada pelo seeder.',
                'valor_total' => 0, // Será calculado com base nos produtos
            ]);

            // Adiciona produtos à venda
            $produtosVenda = $produtos->random(rand(1, 5)); // Entre 1 e 5 produtos por venda
            $valorTotalVenda = 0;

            foreach ($produtosVenda as $produto) {
                $quantidade = rand(1, 10); // Quantidade aleatória entre 1 e 10
                $valorUnitario = $produto->preco ?? 0; // Usa o preço do produto ou 0 se for null
                $valorTotalProduto = $quantidade * $valorUnitario;

                // Adiciona o produto à venda na tabela pivô
                $venda->produtos()->attach($produto->id, [
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'valor_total' => $valorTotalProduto,
                ]);

                // Atualiza o valor total da venda
                $valorTotalVenda += $valorTotalProduto;
            }

            // Atualiza o valor total da venda
            $venda->update(['valor_total' => $valorTotalVenda]);
        }
    }
}
