<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\Clientes;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrcamentoController extends Controller
{
  public function index(Request $request)
  {
      $search = $request->input('search');

      $orcamentos = Orcamento::with('cliente')
          ->when($search, function ($query, $search) {
              return $query->where('id', 'like', "%$search%")
                  ->orWhereHas('cliente', function ($query) use ($search) {
                      $query->where('nome', 'like', "%$search%");
                  });
          })
          ->paginate(10); // Paginação com 10 itens por página

      return view('content.orcamentos.index', compact('orcamentos'));
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
            $orcamento = Orcamento::create($request->only(['cliente_id', 'data', 'validade', 'observacoes']));
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
            ]);

            // Incrementar o valor total
            $valorTotal += $valorTotalProduto;
        }

        // Atualizar o valor total no orçamento
        $orcamento->update(['valor_total' => $valorTotal]);

        DB::commit();

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
    return $pdf->stream($filename);
}


}
