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

    // Substitua 'codigo_da_imagem_base64' pelo seu código base64
    $logoBase64 = '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';

    // Gera o nome do arquivo com o ID do orçamento
    $filename = 'orcamento_' . $orcamento->id . '.pdf';

    // Gera o PDF usando a view e passa os dados necessários
    $pdf = PDF::loadView('content.orcamentos.pdf', compact('orcamento', 'logoBase64'));

    // Retorna o PDF para o navegador
    return $pdf->stream($filename);
}


}
