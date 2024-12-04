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
    // Exibir a listagem dos orçamentos
    public function index()
    {
        $orcamentos = Orcamento::with('cliente')->get();
        return view('orcamentos.index', compact('orcamentos'));
    }

    // Exibir o formulário de criação de um novo orçamento
    public function create()
    {
        $clientes = Clientes::with('endereco')->get();
        $produtos = Produto::all();
        return view('content.orcamentos.criar', compact('clientes', 'produtos'));
    }

    // Armazenar um novo orçamento
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
            'produtos.*.quantidade' => 'required|numeric|min:1|max:10000', // Limitar a quantidade para evitar valores absurdamente altos
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

            // Extrair o valor do serviço, se houver, antes do loop de produtos
            $valorServico = isset($request->produtos['servico'])
                ? floatval(str_replace(['R$', '.', ','], ['', '', '.'], $request->produtos['servico']['valor_unitario']))
                : 0;

            // Atualizar o valor do serviço no orçamento, se houver
            if ($valorServico > 0) {
                $orcamento->update(['valor_servico' => $valorServico]);
                Log::info('Valor do serviço atualizado no orçamento', ['valor_servico' => $valorServico]);
            }

            // Processar produtos e calcular o valor total
            foreach ($request->produtos as $key => $produto) {
                // Pular o serviço na iteração do loop
                if ($key === 'servico') {
                    $valorTotal += $valorServico; // Adicionar o valor do serviço ao valor total
                    continue;
                }

                // Tratamento do valor_unitario para garantir que está no formato correto
                $valorUnitario = str_replace(['R$', '.', ','], ['', '', '.'], $produto['valor_unitario']);
                $valorUnitario = floatval($valorUnitario);

                // Calcular valor total para o produto atual
                $quantidade = intval($produto['quantidade']);
                $valorTotalProduto = $quantidade * $valorUnitario;

                // Produto existente: associar ao orçamento
                $orcamento->produtos()->attach($produto['id'], [
                    'quantidade' => $quantidade,
                    'valor_unitario' => $valorUnitario,
                ]);

                // Somar o valor do produto ao valor total do orçamento
                $valorTotal += $valorTotalProduto;
            }

            // Atualizar o valor total do orçamento após somar produtos e serviços
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


    // Exportar o orçamento em PDF
    public function exportarPdf($id)
    {
        $orcamento = Orcamento::with(['cliente', 'produtos'])->findOrFail($id);
        $pdf = Pdf::loadView('orcamentos.pdf', compact('orcamento'));
        return $pdf->download('orcamento-' . $orcamento->id . '.pdf');
    }

    // Obter coordenadas do endereço do cliente
    public function obterCoordenadas(Request $request)
    {
        $request->validate([
            'endereco_cliente' => 'required|string',
        ]);

        $endereco = $request->input('endereco_cliente');
        $apiKey = env('GOOGLE_GEOCODING_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($endereco) . "&key={$apiKey}";

        $response = file_get_contents($url);
        $data = json_decode($response);

        if ($data->status !== 'OK') {
            return response()->json(['error' => 'Erro ao obter coordenadas.']);
        }

        $lat = $data->results[0]->geometry->location->lat;
        $lng = $data->results[0]->geometry->location->lng;

        return response()->json(['lat' => $lat, 'lng' => $lng]);
    }
}
