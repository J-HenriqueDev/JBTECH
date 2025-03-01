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

class VendaController extends Controller
{
    /**
     * Exibe a lista de vendas.
     */
    public function index()
    {
        // Recupera todas as vendas com o relacionamento de cliente
        $vendas = Venda::with('cliente')->get();

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

            // Recupera o nome do cliente
            $cliente = Clientes::find($request->cliente_id);

            // Mensagem de sucesso
            $mensagemSucesso = "Venda #{$venda->id} para o cliente {$cliente->nome} foi processada com sucesso!";

            // Redireciona para a rota de vendas com a mensagem de sucesso
            return redirect()->route('vendas.index')->with('success', $mensagemSucesso);
        } catch (\Exception $e) {
            Log::error('Erro ao processar a venda:', ['error' => $e->getMessage()]);
            return back()->withErrors('Erro ao processar a venda. Por favor, tente novamente.');
        }
    }

    /**
     * Exibe os detalhes de uma venda específica.
     */
    public function show(Venda $venda)
    {
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

            // Redireciona para a rota de listagem de vendas com mensagem de sucesso
            return redirect()->route('vendas.index')->with('success', 'Venda atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar a venda:', ['error' => $e->getMessage()]);
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

        // Redireciona com mensagem de sucesso
        return redirect()->route('vendas.index')->with('success', 'Venda excluída com sucesso!');
    }

    /**
     * Gera uma cobrança para a venda.
     */
    public function gerarCobranca($id)
    {
        // Recupera a venda
        $venda = Venda::with(['cliente'])->findOrFail($id);

        // Configura o ambiente do PagSeguro
        Library::initialize();
        $env = env('PAGSEGURO_ENV', 'sandbox'); // 'sandbox' ou 'production'
        Configure::setEnvironment($env);
        Configure::setAccountCredentials(env('PAGSEGURO_EMAIL'), env('PAGSEGURO_TOKEN'));

        // Cria a cobrança
        $payment = new Payment();
        $payment->addItems()->withParameters(
            '001', // ID do item
            'Venda #' . $venda->id, // Descrição
            1, // Quantidade
            $venda->valor_total // Valor
        );
        $payment->setCurrency('BRL');
        $payment->setReference($venda->id); // Referência da venda
        $payment->setRedirectUrl(route('vendas.show', $venda->id)); // URL de redirecionamento
        $payment->setNotificationUrl(route('pagseguro.notification')); // URL de notificação

        // Define os dados do cliente
        $payment->setSender()->setName($venda->cliente->nome);
        $payment->setSender()->setEmail($venda->cliente->email);

        // Define o método de pagamento
        $method = request()->input('metodoPagamento'); // PIX ou Boleto
        if ($method === 'pix') {
            $payment->setPaymentMethod('pix');
        } elseif ($method === 'boleto') {
            $payment->setPaymentMethod('boleto');
        }

        try {
            // Envia a cobrança
            $response = $payment->register(Configure::getAccountCredentials());

            // Verifica se o e-mail deve ser enviado
            if (request()->input('enviarEmail')) {
                // Gera o PDF da venda
                $pdf = Pdf::loadView('vendas.pdf', compact('venda'));

                // Envia o e-mail com o PDF
                Mail::to($venda->cliente->email)->send(new CobrancaEnviada($venda, $pdf));
            }

            // Retorna a URL de redirecionamento
            return response()->json([
                'redirectUrl' => $response->getRedirectUrl()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao gerar cobrança: ' . $e->getMessage()
            ], 500);
        }
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

        // Retorna o PDF para visualização
        return $pdf->stream('venda.pdf');
    }
}
