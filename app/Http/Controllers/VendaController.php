<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\Clientes;
use App\Models\Produto;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class VendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Recupera todas as vendas com o relacionamento de cliente
        $vendas = Venda::with('cliente')->get();

        // Passa as vendas para a view
        return view('content.vendas.index', compact('vendas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Clientes::all();
        $produtos = Produto::all();
        return view('content.vendas.criar', compact('clientes','produtos'));
    }


    public function store(Request $request)
    {
        Log::info('Método store foi chamado.');
        Log::info('Dados recebidos no request:', $request->all());

        try {
            // Validação dos dados
            $validatedData = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'data_venda' => 'required|date',
                'observacoes' => 'nullable|string',
                'produtos' => 'required|array',
                'produtos.*.id' => 'required|exists:produtos,id',
                'produtos.*.quantidade' => 'required|integer|min:1',
                'produtos.*.valor_unitario' => 'required|string',
            ]);

            Log::info('Validação passou com sucesso:', $validatedData);

            // Cria a venda
            $venda = Venda::create([
                'cliente_id' => $request->cliente_id,
                'user_id' => Auth::user()->id,
                'data_venda' => $request->data_venda,
                'observacoes' => $request->observacoes,
                'valor_total' => 0, // Inicializa o valor total como 0
            ]);

            Log::info('Venda criada:', $venda->toArray());

            // Adiciona os produtos à venda (tabela pivô) e calcula o valor total
            $valorTotalVenda = 0; // Inicializa o valor total da venda

            foreach ($request->produtos as $produto) {
                $valorUnitario = str_replace(['R$', '.', ','], ['', '', '.'], $produto['valor_unitario']);
                $quantidade = $produto['quantidade'];
                $valorTotal = (float) $valorUnitario * $quantidade; // Calcula o valor total do produto

                $venda->produtos()->attach($produto['id'], [
                    'quantidade' => $quantidade,
                    'valor_unitario' => (float) $valorUnitario,
                    'valor_total' => $valorTotal, // Armazena o valor total do produto
                ]);

                $valorTotalVenda += $valorTotal; // Soma ao valor total da venda

                Log::info('Produto adicionado à venda:', [
                    'venda_id' => $venda->id,
                    'produto_id' => $produto['id'],
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                    'valor_total' => $valorTotal,
                ]);
            }

            // Atualiza o valor total da venda
            $venda->valor_total = $valorTotalVenda;
            $venda->save();

            Log::info('Valor total da venda atualizado:', ['valor_total' => $venda->valor_total]);
            Log::info('Todos os produtos foram adicionados à venda.');

            // Redireciona para a rota de exportação do PDF com o ID da venda
            return redirect()->route('vendas.exportarPdf', ['id' => $venda->id]);
        } catch (\Exception $e) {
            Log::error('Erro ao processar a venda:', ['error' => $e->getMessage()]);
            return back()->withErrors('Erro ao processar a venda. Por favor, tente novamente.');
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Venda $venda)
    {
        //
    }

    public function edit($id)
    {
        // Recupera a venda com os relacionamentos de cliente e produtos
        $venda = Venda::with(['cliente', 'produtos'])->findOrFail($id);

        // Recupera todos os clientes e produtos para os selects
        $clientes = Clientes::all();
        $produtos = Produto::all();

        // Passa os dados para a view
        return view('content.vendas.editar', compact('venda', 'clientes', 'produtos'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Método update foi chamado.');
        Log::info('Dados recebidos no request:', $request->all());

        try {
            // Validação dos dados
            $validatedData = $request->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'data_venda' => 'required|date',
                'observacoes' => 'nullable|string',
                'produtos' => 'required|json', // Valida se é um JSON válido
            ]);

            // Decodifica os produtos
            $produtos = json_decode($request->produtos, true);

            Log::info('Validação passou com sucesso:', $validatedData);

            // Recupera a venda existente
            $venda = Venda::findOrFail($id);

            // Atualiza os dados da venda
            $venda->update([
                'cliente_id' => $request->cliente_id,
                'data_venda' => $request->data_venda,
                'observacoes' => $request->observacoes,
            ]);

            Log::info('Venda atualizada:', $venda->toArray());

            // Prepara os dados dos produtos para o sync
            $produtosSync = [];
            $valorTotalVenda = 0;

            foreach ($produtos as $produto) {
                $valorUnitario = str_replace(['R$', '.', ','], ['', '', '.'], $produto['valor_unitario']);
                $quantidade = $produto['quantidade'];
                $valorTotal = (float) $valorUnitario * $quantidade;

                $produtosSync[$produto['id']] = [
                    'quantidade' => $quantidade,
                    'valor_unitario' => (float) $valorUnitario,
                    'valor_total' => $valorTotal,
                ];

                $valorTotalVenda += $valorTotal;
            }

            // Sincroniza os produtos da venda
            $venda->produtos()->sync($produtosSync);

            // Atualiza o valor total da venda
            $venda->valor_total = $valorTotalVenda;
            $venda->save();

            Log::info('Valor total da venda atualizado:', ['valor_total' => $venda->valor_total]);
            Log::info('Todos os produtos foram atualizados na venda.');

            // Redireciona para a rota de listagem de vendas com mensagem de sucesso
            return redirect()->route('vendas.index')->with('success', 'Venda atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar a venda:', ['error' => $e->getMessage()]);
            return back()->withErrors('Erro ao atualizar a venda. Por favor, tente novamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venda $venda)
    {
        //
    }

    public function exportarPdf($id)
    {
        // Busca a venda com os relacionamentos
        $venda = Venda::with(['cliente', 'produtos'])->findOrFail($id);

        // Converte o logo para base64
        $logoBase64 = base64_encode(file_get_contents(public_path('assets/img/front-pages/landing-page/jblogo_black.png')));

        // Gera o PDF
        $pdf = PDF::loadView('content.vendas.pdf', compact('venda', 'logoBase64'));

        // Retorna o PDF para visualização
        return $pdf->stream('venda.pdf');
    }
}
