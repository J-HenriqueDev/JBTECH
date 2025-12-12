<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Clientes;
use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use App\Services\LogService;


class OrcamentoController extends Controller
{
  public function index(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('status'); // Array de status
    $data_inicio = $request->input('data_inicio');
    $data_fim = $request->input('data_fim');

    // Ordenação padrão pelos mais recentes primeiro
    $ordenacao = $request->input('ordenacao', 'recentes');

    $query = Orcamento::with(['cliente', 'usuario']);

    // Filtro de busca
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('id', 'like', "%$search%")
                ->orWhereHas('cliente', function ($q) use ($search) {
                    $q->where('nome', 'like', "%$search%")
                      ->orWhere('cpf_cnpj', 'like', "%$search%");
                });
        });
    }

    // Filtro de status
    if ($status && is_array($status) && count($status) > 0) {
        $query->whereIn('status', $status);
    } else {
        // Exclui orçamentos com status "Apagado" por padrão
        $query->where('status', '!=', 'apagado');
    }

    // Filtro de data
    if ($data_inicio) {
        $query->whereDate('data', '>=', $data_inicio);
    }
    if ($data_fim) {
        $query->whereDate('data', '<=', $data_fim);
    }

    // Ordenação
    switch ($ordenacao) {
        case 'recentes':
            $query->orderBy('updated_at', 'desc');
            break;
        case 'antigos':
            $query->orderBy('updated_at', 'asc');
            break;
        case 'maior_valor':
            $query->orderBy('valor_total', 'desc');
            break;
        case 'menor_valor':
            $query->orderBy('valor_total', 'asc');
            break;
        default:
            $query->orderBy('updated_at', 'desc');
    }

    $orcamentos = $query->paginate(15);

    // Estatísticas
    $stats = [
        'total' => Orcamento::where('status', '!=', 'apagado')->count(),
        'pendentes' => Orcamento::pendentes()->count(),
        'autorizados' => Orcamento::autorizados()->count(),
        'recusados' => Orcamento::recusados()->count(),
        'vencidos' => Orcamento::where('validade', '<', now())
            ->where('status', '!=', 'apagado')
            ->where('status', '!=', 'autorizado')
            ->count(),
        'valor_total_pendente' => Orcamento::pendentes()->sum('valor_total'),
        'valor_total_autorizado' => Orcamento::autorizados()->sum('valor_total'),
    ];

    return view('content.orcamentos.index', compact('orcamentos', 'stats'));
}

    public function show($id)
    {
        $orcamento = Orcamento::with([
            'cliente.endereco',
            'produtos.categoria',
            'usuario'
        ])->findOrFail($id);

        // Verificar se pode ser autorizado
        $podeAutorizar = $orcamento->podeSerAutorizado();
        $produtosSemEstoque = [];
        
        if (!$podeAutorizar) {
            foreach ($orcamento->produtos as $produto) {
                if ($produto->estoque < $produto->pivot->quantidade) {
                    $produtosSemEstoque[] = [
                        'produto' => $produto,
                        'estoque_disponivel' => $produto->estoque,
                        'quantidade_solicitada' => $produto->pivot->quantidade,
                        'faltam' => $produto->pivot->quantidade - $produto->estoque,
                    ];
                }
            }
        }

        return view('content.orcamentos.show', compact('orcamento', 'podeAutorizar', 'produtosSemEstoque'));
    }

    public function create()
    {
        $clientes = Clientes::with('endereco')->get();
        $produtos = Produto::all();
        return view('content.orcamentos.criar', compact('clientes', 'produtos'));
    }

    public function store(Request $request)
{
    // Logar todos os dados recebidos para verificar
    Log::info('Dados recebidos do request:', $request->all());

    // Validação dos campos
    $validated = $request->validate([
        'cliente_id' => 'required|exists:clientes,id',
        'data' => 'required|date',
        'validade' => 'required|date|after_or_equal:data',
        'observacoes' => 'nullable|string',
        'produtos' => 'required|array',
        'produtos.*.quantidade' => 'required|numeric|min:1|max:10000', // Limitar a quantidade
        'produtos.*.valor_unitario' => 'required|string',
    ]);

    // Iniciar a transação com DB facade
    DB::beginTransaction();
    try {
        // Criar o orçamento sem o valor do serviço inicialmente
        $orcamento = Orcamento::create(array_merge(
            $request->only(['cliente_id', 'data', 'validade', 'observacoes']),
            ['user_id' => Auth::id(), 'status' => Orcamento::STATUS_PENDENTE]
        ));
        Log::info('Orçamento criado', ['orcamento' => $orcamento]);

        // Inicializar valor total do orçamento
        $valorTotal = 0;

        // Processar produtos e calcular o valor total
        foreach ($request->produtos as $id => $produto) {
            // Tratamento do valor_unitario para garantir que está no formato correto
            $valorUnitario = str_replace(['R$', '.', ','], ['', '', '.'], $produto['valor_unitario']);
            $valorUnitario = floatval($valorUnitario);

            // Calcular valor total para o produto atual
            $quantidade = intval($produto['quantidade']);
            $valorTotalProduto = $quantidade * $valorUnitario;

            // Associar produto ao orçamento
            $orcamento->produtos()->attach($id, [
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorTotalProduto, // Adiciona o valor_total aqui
            ]);

            // Somar o valor do produto ao valor total do orçamento
            $valorTotal += $valorTotalProduto;
        }

        // Atualizar o valor total do orçamento após somar produtos
        $orcamento->update(['valor_total' => $valorTotal]);

        Log::info('Valor total do orçamento atualizado', ['valor_total' => $valorTotal]);

        // Confirmar a transação
        DB::commit();
        Log::info('Transação concluída com sucesso');

        $request->merge(['log_detalhes' => "Orçamento ID: {$orcamento->id}"]);

        LogService::registrar(
            'Orçamento', // Categoria
            'Criar', // Ação
            "Orçamento ID: {$orcamento->id}" // Detalhes
        );
        return redirect()->route('orcamentos.index')->with('success', 'Orçamento criado com sucesso!');
    } catch (\Exception $e) {
        // Reverter a transação em caso de erro
        DB::rollBack();
        Log::error('Erro ao criar orçamento', ['message' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
        return redirect()->back()->withErrors('Erro ao criar orçamento. Tente novamente mais tarde.');
    }
}

    public function obterCoordenadas(Request $request)
    {
        $request->validate([
            'endereco_cliente' => 'required|string',
        ]);

        $endereco = $request->input('endereco_cliente');
        $apiKey = env('GOOGLE_GEOCODING_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($endereco) . "&key={$apiKey}";

        try {
            $response = file_get_contents($url);
            $data = json_decode($response);

            if ($data->status !== 'OK') {
                throw new \Exception('Erro ao obter coordenadas do endereço');
            }

            $lat = $data->results[0]->geometry->location->lat;
            $lng = $data->results[0]->geometry->location->lng;

            return response()->json(['lat' => $lat, 'lng' => $lng]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter coordenadas:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Não foi possível obter coordenadas. Verifique o endereço e tente novamente.']);
        }
    }
    public function search(Request $request)
    {
        $search = $request->input('search');

        $orcamentos = Orcamento::with('cliente')
            ->when($search, function ($query, $search) {
                return $query->where('id', 'like', "%$search%")
                    ->orWhereHas('cliente', function ($query) use ($search) {
                        $query->where('nome', 'like', "%$search%");
                    });
            })
            ->get();

        return response()->json($orcamentos);
    }
    public function edit($id)
    {
        $orcamento = Orcamento::with(['cliente', 'produtos'])->findOrFail($id);
        $clientes = Clientes::all();
        $produtos = Produto::all();

        return view('content.orcamentos.editar', compact('orcamento', 'clientes', 'produtos'));
    }


    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'cliente_id' => 'required|exists:clientes,id',
        'data' => 'required|date',
        'validade' => 'required|date|after_or_equal:data',
        'observacoes' => 'nullable|string',
        'produtos' => 'required|array',
        'produtos.*.quantidade' => 'required|integer|min:1',
        'produtos.*.valor_unitario' => 'required|string',
    ]);

    DB::beginTransaction();

    try {
        // Localizar o orçamento e atualizar os campos principais
        $orcamento = Orcamento::findOrFail($id);
        $orcamento->update($request->only(['cliente_id', 'data', 'validade', 'observacoes']));

        // Remover os produtos antigos associados ao orçamento
        $orcamento->produtos()->detach();

        // Inicializar o valor total
        $valorTotal = 0;

        // Reassociar os produtos e calcular o valor total
        foreach ($request->produtos as $produtoId => $produto) {
            $valorUnitario = floatval(str_replace(['R$', '.', ','], ['', '', '.'], $produto['valor_unitario']));
            $quantidade = intval($produto['quantidade']);
            $valorTotalProduto = $valorUnitario * $quantidade;

            $orcamento->produtos()->attach($produtoId, [
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorTotalProduto,
            ]);

            // Incrementar o valor total
            $valorTotal += $valorTotalProduto;
        }

        // Atualizar o valor total no orçamento
        $orcamento->update(['valor_total' => $valorTotal]);

        DB::commit();
        // Registra um log
        $request->merge(['log_detalhes' => "Orçamento ID: {$orcamento->id}"]);

        // Registra um log
          LogService::registrar(
            'Orçamento', // Categoria
            'Editar', // Ação
            "Orçamento ID: {$orcamento->id}" // Detalhes
        );
        return redirect()->route('content.orcamentos.index')->with('success', 'Orçamento atualizado com sucesso!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors('Erro ao atualizar orçamento. Tente novamente mais tarde.');
    }
}

public function gerarPdf($id)
{
    // Busca o orçamento com base no ID
    $orcamento = Orcamento::with(['cliente', 'produtos'])->findOrFail($id);


    $filename = 'orcamento_' . $orcamento->id . '.pdf';

    // Gera o PDF usando a view e passa os dados necessários
    $pdf = PDF::loadView('content.orcamentos.pdf', compact('orcamento'));

    // Retorna o PDF para o navegador
        // Registra um log
        LogService::registrar(
          'Orçamento', // Categoria
          'Gerar PDF', // Ação
          "Orçamento ID: {$orcamento->id}" // Detalhes
      );
    return $pdf->stream($filename);
}

public function autorizar($id)
{
    DB::beginTransaction();
    try {
        Log::info('Iniciando autorização do orçamento:', ['orcamento_id' => $id]);

        $orcamento = Orcamento::findOrFail($id);
        Log::info('Orçamento encontrado:', $orcamento->toArray());

        // Verifica se há estoque suficiente antes de autorizar
        if (!$orcamento->podeSerAutorizado()) {
            $produtosSemEstoque = [];
            foreach ($orcamento->produtos as $produto) {
                if ($produto->estoque < $produto->pivot->quantidade) {
                    $produtosSemEstoque[] = $produto->nome . ' (Estoque: ' . $produto->estoque . ', Solicitado: ' . $produto->pivot->quantidade . ')';
                }
            }
            return redirect()->back()->withErrors('Não é possível autorizar o orçamento. Estoque insuficiente para: ' . implode(', ', $produtosSemEstoque));
        }

        // Verifica se já existe uma venda duplicada para o mesmo cliente com os mesmos produtos
        $vendaExistente = Venda::where('cliente_id', $orcamento->cliente_id)
            ->whereHas('produtos', function ($query) use ($orcamento) {
                // Verifica se todos os produtos do orçamento estão na venda existente
                $produtosOrcamento = $orcamento->produtos->pluck('id')->toArray();
                $query->whereIn('produto_id', $produtosOrcamento);
            })
            ->with('produtos')
            ->first();

        if ($vendaExistente) {
            // Verifica se os produtos da venda existente são iguais aos do orçamento
            $produtosVendaExistente = $vendaExistente->produtos->pluck('id')->toArray();
            $produtosOrcamento = $orcamento->produtos->pluck('id')->toArray();

            if (empty(array_diff($produtosVendaExistente, $produtosOrcamento))) {
                Log::warning('Venda duplicada encontrada. Atualizando status do orçamento para "autorizado".', [
                    'venda_id' => $vendaExistente->id,
                    'orcamento_id' => $orcamento->id,
                ]);

                // Atualiza o status do orçamento para "autorizado"
                $orcamento->update(['status' => Orcamento::STATUS_AUTORIZADO]);

                DB::commit();

                return redirect()->route('orcamentos.index')->with('success', 'Orçamento autorizado (venda duplicada encontrada).');
            }
        }

        // Cria a venda a partir do orçamento
        $venda = Venda::create([
            'cliente_id' => $orcamento->cliente_id,
            'user_id' => Auth::user()->id,
            'data_venda' => now(),
            'observacoes' => $orcamento->observacoes,
            'valor_total' => $orcamento->valor_total,
        ]);
        Log::info('Venda criada:', $venda->toArray());

        // Adiciona os produtos da venda (tabela pivô) e atualiza o estoque
        foreach ($orcamento->produtos as $produto) {
            $valorUnitario = $produto->pivot->valor_unitario;
            $quantidade = $produto->pivot->quantidade;
            $valorTotal = $valorUnitario * $quantidade;

            $venda->produtos()->attach($produto->id, [
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorTotal,
            ]);

            // Atualiza o estoque do produto (pode ficar negativo)
            $produto->estoque -= $quantidade;
            $produto->save();

            Log::info('Produto adicionado à venda e estoque atualizado:', [
                'venda_id' => $venda->id,
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorTotal,
                'novo_estoque' => $produto->estoque,
            ]);
        }

        // Atualiza o status do orçamento para "autorizado"
        $orcamento->update(['status' => Orcamento::STATUS_AUTORIZADO]);
        Log::info('Status do orçamento atualizado para "autorizado".');

        DB::commit();

        // Recupera o nome do cliente
        $cliente = Clientes::find($orcamento->cliente_id);

        // Mensagem de sucesso
        $mensagemSucesso = "Orçamento #{$orcamento->id} autorizado e transformado em venda #{$venda->id} para o cliente {$cliente->nome}.";
        Log::info('Orçamento autorizado com sucesso:', ['mensagem' => $mensagemSucesso]);

              // Registra um log
          LogService::registrar(
            'Orçamento', // Categoria
            'Autorizar', // Ação
            "Orçamento ID: {$orcamento->id}" // Detalhes
        );

        return redirect()->route('orcamentos.index')->with('success', $mensagemSucesso);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erro ao autorizar orçamento:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return redirect()->back()->withErrors('Erro ao autorizar orçamento. Tente novamente mais tarde.');
    }
}

public function recusar($id)
{
    DB::beginTransaction();
    try {
        $orcamento = Orcamento::findOrFail($id);

        // Verifica se há uma venda associada ao orçamento
        $venda = Venda::where('cliente_id', $orcamento->cliente_id)
            ->whereHas('produtos', function ($query) use ($orcamento) {
                $produtosOrcamento = $orcamento->produtos->pluck('id')->toArray();
                $query->whereIn('produto_id', $produtosOrcamento);
            })
            ->first();

        if ($venda) {
            // Exclui a venda associada
            $venda->produtos()->detach(); // Remove os produtos da tabela pivô
            $venda->delete(); // Exclui a venda
            Log::info('Venda excluída:', ['venda_id' => $venda->id]);
        }

        // Atualiza o status do orçamento para "recusado"
        $orcamento->update(['status' => Orcamento::STATUS_RECUSADO]);
        Log::info('Orçamento recusado:', ['orcamento_id' => $orcamento->id]);

        DB::commit();
            // Registra um log
        LogService::registrar(
          'Orçamento', // Categoria
          'Recusar', // Ação
          "Orçamento ID: {$orcamento->id}" // Detalhes
      );
        return redirect()->back()->with('success', 'Orçamento recusado e venda associada excluída com sucesso!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erro ao recusar orçamento:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return redirect()->back()->withErrors('Erro ao recusar orçamento. Tente novamente mais tarde.');
    }
}

    public function verificarEstoque($id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $estoqueInsuficiente = false;

        foreach ($orcamento->produtos as $produto) {
            if ($produto->estoque < $produto->pivot->quantidade) {
                $estoqueInsuficiente = true;
                break;
            }
        }

        return response()->json([
            'estoqueInsuficiente' => $estoqueInsuficiente,
        ]);
    }

    public function destroy($id)
    {
        // Encontra o orçamento pelo ID
        $orcamento = Orcamento::findOrFail($id);

        // Atualiza o status para "Apagado"
        $orcamento->update(['status' => 'apagado']);

          // Registra um log
        LogService::registrar(
          'Orçamento', // Categoria
          'Excluir', // Ação
          "Orçamento ID: {$orcamento->id}" // Detalhes
      );

        // Redireciona de volta com uma mensagem de sucesso
        return redirect()->route('orcamentos.index')->with('success', 'Orçamento marcado como Apagado com sucesso!');
    }

}
