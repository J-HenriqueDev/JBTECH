<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Clientes;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PagSeguro\Configuration\Configure;
use PagSeguro\Domains\Requests\Payment;
use PagSeguro\Library;
use Illuminate\Support\Facades\Mail;
use App\Mail\CobrancaEnviada;
use App\Services\LogService;

class VendaController extends Controller
{
    /**
     * Exibe a lista de vendas.
     */
    public function index()
    {
        // Recupera todas as vendas com o relacionamento de cliente
        $vendas = Venda::with('cliente')->get();

        // Registra um log
        LogService::registrar(
            'Venda', // Categoria
            'Listar', // Ação
            'Listou todas as vendas' // Detalhes
        );

        // Passa as vendas para a view
        return view('content.vendas.index', compact('vendas'));
    }

    /**
     * Exibe o formulário para criar uma nova venda.
     */
    public function create()
    {
        $clientes = Clientes::all();
        $produtos = Produto::all();

        // Registra um log
        LogService::registrar(
            'Venda', // Categoria
            'Criar', // Ação
            'Acessou o formulário de criação de venda' // Detalhes
        );

        return view('content.vendas.criar', compact('clientes', 'produtos'));
    }

    /**
     * Armazena uma nova venda no banco de dados.
     */
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

            // Registra um log
            LogService::registrar(
                'Venda', // Categoria
                'Criar', // Ação
                "Venda ID: {$venda->id} criada com sucesso" // Detalhes
            );

            // Recupera o nome do cliente
            $cliente = Clientes::find($request->cliente_id);

            // Mensagem de sucesso
            $mensagemSucesso = "Venda #{$venda->id} para o cliente {$cliente->nome} foi processada com sucesso!";

            // Redireciona para a rota de vendas com a mensagem de sucesso
            return redirect()->route('vendas.index')->with('success', $mensagemSucesso);
        } catch (\Exception $e) {
            Log::error('Erro ao processar a venda:', ['error' => $e->getMessage()]);

            // Registra um log de erro
            LogService::registrar(
                'Venda', // Categoria
                'Erro', // Ação
                "Erro ao criar venda: {$e->getMessage()}" // Detalhes
            );

            return back()->withErrors('Erro ao processar a venda. Por favor, tente novamente.');
        }
    }

    /**
     * Exibe os detalhes de uma venda específica.
     */
    public function show(Venda $venda)
    {
        // Registra um log
        LogService::registrar(
            'Venda', // Categoria
            'Visualizar', // Ação
            "Visualizou a venda ID: {$venda->id}" // Detalhes
        );

        // Exibe a view de detalhes da venda
        return view('content.vendas.show', compact('venda'));
    }

    /**
     * Exibe o formulário para editar uma venda.
     */
    public function edit($id)
    {
        // Recupera a venda com os relacionamentos de cliente e produtos
        $venda = Venda::with(['cliente', 'produtos'])->findOrFail($id);

        // Recupera todos os clientes e produtos para os selects
        $clientes = Clientes::all();
        $produtos = Produto::all();

        // Registra um log
        LogService::registrar(
            'Venda', // Categoria
            'Editar', // Ação
            "Acessou o formulário de edição da venda ID: {$venda->id}" // Detalhes
        );

        // Passa os dados para a view
        return view('content.vendas.editar', compact('venda', 'clientes', 'produtos'));
    }

    /**
     * Atualiza uma venda no banco de dados.
     */
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

            // Registra um log
            LogService::registrar(
                'Venda', // Categoria
                'Editar', // Ação
                "Venda ID: {$venda->id} atualizada com sucesso" // Detalhes
            );

            // Redireciona para a rota de listagem de vendas com mensagem de sucesso
            return redirect()->route('vendas.index')->with('success', 'Venda atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar a venda:', ['error' => $e->getMessage()]);

            // Registra um log de erro
            LogService::registrar(
                'Venda', // Categoria
                'Erro', // Ação
                "Erro ao atualizar venda ID: {$id}: {$e->getMessage()}" // Detalhes
            );

            return back()->withErrors('Erro ao atualizar a venda. Por favor, tente novamente.');
        }
    }

    /**
     * Remove uma venda do banco de dados.
     */
    public function destroy(Venda $venda)
    {
        // Exclui a venda
        $venda->delete();

        // Registra um log
        LogService::registrar(
            'Venda', // Categoria
            'Excluir', // Ação
            "Venda ID: {$venda->id} excluída com sucesso" // Detalhes
        );

        // Redireciona com mensagem de sucesso
        return redirect()->route('vendas.index')->with('success', 'Venda excluída com sucesso!');
    }

    /**
     * Exporta a venda para PDF.
     */
    public function exportarPdf($id)
    {
        // Busca a venda com os relacionamentos
        $venda = Venda::with(['cliente', 'produtos'])->findOrFail($id);

        // Converte o logo para base64
        $logoBase64 = base64_encode(file_get_contents(public_path('assets/img/front-pages/landing-page/jblogo_black.png')));

        // Gera o PDF
        $pdf = PDF::loadView('content.vendas.pdf', compact('venda', 'logoBase64'));

        // Registra um log
        LogService::registrar(
            'Venda', // Categoria
            'Exportar PDF', // Ação
            "Exportou a venda ID: {$venda->id} para PDF" // Detalhes
        );

        // Retorna o PDF para visualização
        return $pdf->stream('venda.pdf');
    }
}
