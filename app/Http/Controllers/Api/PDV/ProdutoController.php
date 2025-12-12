<?php

namespace App\Http\Controllers\Api\PDV;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::with('categoria')
            ->where('ativo_pdv', true)
            ->where('estoque', '>', 0);

        // Busca por código de barras
        if ($request->has('codigo_barras')) {
            $query->where('codigo_barras', $request->codigo_barras);
        }

        // Busca por nome
        if ($request->has('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        // Busca por ID
        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        $produtos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $produtos
        ]);
    }

    public function sincronizar(Request $request)
    {
        $produtos = $request->input('produtos', []);

        foreach ($produtos as $produtoData) {
            Produto::updateOrCreate(
                ['id' => $produtoData['id']],
                [
                    'nome' => $produtoData['nome'],
                    'preco_venda' => $produtoData['preco_venda'],
                    'codigo_barras' => $produtoData['codigo_barras'] ?? null,
                    'estoque' => $produtoData['estoque'] ?? 0,
                    'ativo_pdv' => $produtoData['ativo_pdv'] ?? true,
                    'sincronizado' => true,
                    'ultima_sincronizacao' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Produtos sincronizados com sucesso'
        ]);
    }

    public function atualizarEstoque(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $produto->estoque = $request->estoque;
        $produto->save();

        return response()->json([
            'success' => true,
            'data' => $produto
        ]);
    }
}


