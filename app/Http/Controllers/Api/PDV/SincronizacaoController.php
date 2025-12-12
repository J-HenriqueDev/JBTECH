<?php

namespace App\Http\Controllers\Api\PDV;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;

class SincronizacaoController extends Controller
{
    public function produtos(Request $request)
    {
        $ultimaSincronizacao = $request->input('ultima_sincronizacao');

        $query = Produto::where('ativo_pdv', true);

        if ($ultimaSincronizacao) {
            $query->where(function($q) use ($ultimaSincronizacao) {
                $q->where('ultima_sincronizacao', '>', $ultimaSincronizacao)
                  ->orWhereNull('ultima_sincronizacao');
            });
        }

        $produtos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $produtos,
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function enviarVendas(Request $request)
    {
        $vendas = $request->input('vendas', []);

        foreach ($vendas as $vendaData) {
            // Verifica se a venda já existe
            $venda = Venda::where('numero_cupom', $vendaData['numero_cupom'])->first();

            if (!$venda) {
                // Cria nova venda
                $venda = Venda::create([
                    'caixa_id' => $vendaData['caixa_id'] ?? null,
                    'cliente_id' => $vendaData['cliente_id'] ?? null,
                    'user_id' => $vendaData['user_id'],
                    'data_venda' => $vendaData['data_venda'],
                    'valor_total' => $vendaData['valor_total'],
                    'forma_pagamento' => $vendaData['forma_pagamento'],
                    'valor_recebido' => $vendaData['valor_recebido'],
                    'troco' => $vendaData['troco'],
                    'numero_cupom' => $vendaData['numero_cupom'],
                    'observacoes' => $vendaData['observacoes'] ?? null,
                    'status' => $vendaData['status'] ?? 'concluida',
                    'sincronizado' => true,
                    'data_sincronizacao' => now(),
                ]);

                // Adiciona produtos
                if (isset($vendaData['produtos'])) {
                    foreach ($vendaData['produtos'] as $produto) {
                        $venda->produtos()->attach($produto['id'], [
                            'quantidade' => $produto['quantidade'],
                            'valor_unitario' => $produto['valor_unitario'],
                            'valor_total' => $produto['valor_total'],
                        ]);

                        // Atualiza estoque
                        $produtoModel = Produto::find($produto['id']);
                        if ($produtoModel) {
                            $produtoModel->estoque -= $produto['quantidade'];
                            $produtoModel->save();
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendas sincronizadas com sucesso'
        ]);
    }

    public function status()
    {
        $produtosNaoSincronizados = Produto::where('sincronizado', false)->count();
        $vendasNaoSincronizadas = Venda::where('sincronizado', false)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'produtos_nao_sincronizados' => $produtosNaoSincronizados,
                'vendas_nao_sincronizadas' => $vendasNaoSincronizadas,
            ]
        ]);
    }
}


