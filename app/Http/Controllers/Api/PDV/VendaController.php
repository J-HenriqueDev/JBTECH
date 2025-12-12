<?php

namespace App\Http\Controllers\Api\PDV;

use App\Http\Controllers\Controller;
use App\Models\Venda;
use App\Models\Caixa;
use App\Models\Produto;
use App\Models\Configuracao;
use App\Helpers\PDVHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendaController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validate([
                'caixa_id' => 'required|exists:caixas,id',
                'cliente_id' => 'nullable|exists:clientes,id',
                'produtos' => 'required|array|min:1',
                'produtos.*.id' => 'required|exists:produtos,id',
                'produtos.*.quantidade' => 'required|integer|min:1',
                'produtos.*.valor_unitario' => 'required|numeric|min:0',
                'forma_pagamento' => 'required|in:dinheiro,cartao_debito,cartao_credito,pix,outro',
                'valor_recebido' => 'nullable|numeric|min:0',
                'observacoes' => 'nullable|string',
            ]);

            $caixa = Caixa::findOrFail($validated['caixa_id']);
            
            if ($caixa->status !== 'aberto') {
                return response()->json([
                    'success' => false,
                    'message' => 'Caixa não está aberto'
                ], 400);
            }

            // Calcula o valor total
            $valorTotal = 0;
            foreach ($validated['produtos'] as $item) {
                $valorTotal += $item['quantidade'] * $item['valor_unitario'];
            }

            // Calcula o troco
            $troco = 0;
            if ($validated['forma_pagamento'] === 'dinheiro' && isset($validated['valor_recebido'])) {
                $troco = $validated['valor_recebido'] - $valorTotal;
            }

            // Gera número do cupom
            $numeroCupom = 'CUP' . date('Ymd') . str_pad(Venda::whereDate('created_at', today())->count() + 1, 6, '0', STR_PAD_LEFT);

            $operadorId = PDVHelper::getOperadorId($request);
            
            if (!$operadorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Operador não autenticado'
                ], 401);
            }

            // Cria a venda
            $venda = Venda::create([
                'caixa_id' => $caixa->id,
                'cliente_id' => $validated['cliente_id'] ?? null,
                'user_id' => $operadorId,
                'data_venda' => now(),
                'valor_total' => $valorTotal,
                'forma_pagamento' => $validated['forma_pagamento'],
                'valor_recebido' => $validated['valor_recebido'] ?? $valorTotal,
                'troco' => $troco,
                'numero_cupom' => $numeroCupom,
                'observacoes' => $validated['observacoes'] ?? null,
                'status' => 'concluida',
                'sincronizado' => true,
                'data_sincronizacao' => now(),
            ]);

            // Adiciona produtos e atualiza estoque
            $controleEstoque = Configuracao::get('produtos_controle_estoque', '1') == '1';
            $permitirEstoqueNegativo = Configuracao::get('produtos_venda_estoque_negativo', '0') == '1';
            
            foreach ($validated['produtos'] as $item) {
                $produto = Produto::findOrFail($item['id']);
                
                // Verifica estoque apenas se controle de estoque estiver habilitado
                if ($controleEstoque) {
                    if ($produto->estoque < $item['quantidade'] && !$permitirEstoqueNegativo) {
                        throw new \Exception("Estoque insuficiente para o produto {$produto->nome}. Estoque disponível: {$produto->estoque}");
                    }

                    // Atualiza estoque
                    $produto->estoque -= $item['quantidade'];
                    $produto->save();
                }

                // Adiciona à venda
                $valorTotalItem = $item['quantidade'] * $item['valor_unitario'];
                $venda->produtos()->attach($item['id'], [
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'valor_total' => $valorTotalItem,
                ]);
            }

            // Atualiza valores do caixa
            $caixa->valor_total_vendas += $valorTotal;
            $caixa->calcularValorEsperado();
            $caixa->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $venda->load(['produtos', 'cliente', 'caixa']),
                'message' => 'Venda registrada com sucesso'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar venda: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sincronizar(Request $request)
    {
        $vendas = $request->input('vendas', []);

        foreach ($vendas as $vendaData) {
            Venda::updateOrCreate(
                ['numero_cupom' => $vendaData['numero_cupom']],
                [
                    'caixa_id' => $vendaData['caixa_id'],
                    'cliente_id' => $vendaData['cliente_id'] ?? null,
                    'user_id' => $vendaData['user_id'],
                    'data_venda' => $vendaData['data_venda'],
                    'valor_total' => $vendaData['valor_total'],
                    'forma_pagamento' => $vendaData['forma_pagamento'],
                    'valor_recebido' => $vendaData['valor_recebido'],
                    'troco' => $vendaData['troco'],
                    'status' => $vendaData['status'] ?? 'concluida',
                    'sincronizado' => true,
                    'data_sincronizacao' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Vendas sincronizadas com sucesso'
        ]);
    }
}
