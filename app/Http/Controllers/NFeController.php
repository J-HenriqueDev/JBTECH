<?php

namespace App\Http\Controllers;

use App\Models\NotaFiscal;
use App\Models\Venda;
use App\Services\NFeService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NFeController extends Controller
{
    protected $nfeService;

    public function __construct(NFeService $nfeService)
    {
        $this->nfeService = $nfeService;
    }

    /**
     * Lista todas as notas fiscais
     */
    public function index()
    {
        $notasFiscais = NotaFiscal::with(['venda', 'cliente'])
            ->latest()
            ->paginate(15);

        LogService::registrar(
            'NF-e',
            'Listar',
            'Listou todas as notas fiscais'
        );

        return view('content.nfe.index', compact('notasFiscais'));
    }

    /**
     * Exibe o formulário para emitir NF-e a partir de uma venda
     */
    public function create(Request $request)
    {
        $vendaId = $request->get('venda_id');
        $venda = null;

        if ($vendaId) {
            $venda = Venda::with(['cliente.endereco', 'produtos'])->findOrFail($vendaId);
            
            // Verifica se já existe NF-e para esta venda
            $notaExistente = NotaFiscal::where('venda_id', $venda->id)
                ->where('status', 'autorizada')
                ->first();

            if ($notaExistente) {
                return redirect()->route('nfe.show', $notaExistente->id)
                    ->with('info', 'Já existe uma NF-e autorizada para esta venda.');
            }
        }

        $vendas = Venda::with('cliente')
            ->whereDoesntHave('notasFiscais', function ($query) {
                $query->where('status', 'autorizada');
            })
            ->latest()
            ->get();

        LogService::registrar(
            'NF-e',
            'Criar',
            'Acessou o formulário de emissão de NF-e'
        );

        return view('content.nfe.create', compact('venda', 'vendas'));
    }

    /**
     * Emite uma NF-e
     */
    public function store(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
        ]);

        try {
            $venda = Venda::with(['cliente.endereco', 'produtos'])->findOrFail($request->venda_id);

            // Validações
            if (!$venda->cliente->endereco) {
                return back()->withErrors('O cliente não possui endereço cadastrado. Por favor, cadastre o endereço antes de emitir a NF-e.');
            }

            if ($venda->produtos->isEmpty()) {
                return back()->withErrors('A venda não possui produtos. Adicione produtos antes de emitir a NF-e.');
            }

            // Verifica se já existe NF-e autorizada
            $notaExistente = NotaFiscal::where('venda_id', $venda->id)
                ->where('status', 'autorizada')
                ->first();

            if ($notaExistente) {
                return redirect()->route('nfe.show', $notaExistente->id)
                    ->with('info', 'Já existe uma NF-e autorizada para esta venda.');
            }

            // Emite a NF-e
            $notaFiscal = $this->nfeService->emitirNFe($venda);

            LogService::registrar(
                'NF-e',
                'Emitir',
                "NF-e ID: {$notaFiscal->id} emitida para venda ID: {$venda->id}"
            );

            return redirect()->route('nfe.show', $notaFiscal->id)
                ->with('success', 'NF-e emitida com sucesso! Status: ' . $notaFiscal->status);
        } catch (\Exception $e) {
            Log::error('Erro ao emitir NF-e: ' . $e->getMessage());

            LogService::registrar(
                'NF-e',
                'Erro',
                "Erro ao emitir NF-e: {$e->getMessage()}"
            );

            return back()->withErrors('Erro ao emitir NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de uma NF-e
     */
    public function show($id)
    {
        $notaFiscal = NotaFiscal::with(['venda.cliente.endereco', 'venda.produtos', 'cliente.endereco'])
            ->findOrFail($id);

        LogService::registrar(
            'NF-e',
            'Visualizar',
            "Visualizou a NF-e ID: {$notaFiscal->id}"
        );

        return view('content.nfe.show', compact('notaFiscal'));
    }

    /**
     * Cancela uma NF-e
     */
    public function cancelar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'required|string|min:15|max:255',
        ]);

        try {
            $notaFiscal = NotaFiscal::findOrFail($id);

            if (!$notaFiscal->podeCancelar()) {
                return back()->withErrors('Esta NF-e não pode ser cancelada. Status atual: ' . $notaFiscal->status);
            }

            $this->nfeService->cancelarNFe($notaFiscal, $request->justificativa);

            LogService::registrar(
                'NF-e',
                'Cancelar',
                "NF-e ID: {$notaFiscal->id} cancelada"
            );

            return redirect()->route('nfe.show', $notaFiscal->id)
                ->with('success', 'NF-e cancelada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar NF-e: ' . $e->getMessage());

            return back()->withErrors('Erro ao cancelar NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Consulta o status de uma NF-e
     */
    public function consultarStatus($id)
    {
        try {
            $notaFiscal = NotaFiscal::findOrFail($id);

            $this->nfeService->consultarStatus($notaFiscal);

            return redirect()->route('nfe.show', $notaFiscal->id)
                ->with('success', 'Status da NF-e atualizado!');
        } catch (\Exception $e) {
            Log::error('Erro ao consultar status da NF-e: ' . $e->getMessage());

            return back()->withErrors('Erro ao consultar status: ' . $e->getMessage());
        }
    }

    /**
     * Baixa o XML da NF-e
     */
    public function downloadXml($id)
    {
        $notaFiscal = NotaFiscal::findOrFail($id);

        if (!$notaFiscal->xml) {
            return back()->withErrors('XML da NF-e não disponível.');
        }

        $filename = 'NFe_' . ($notaFiscal->chave_acesso ?? $notaFiscal->id) . '.xml';

        return response($notaFiscal->xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Baixa o XML de cancelamento
     */
    public function downloadXmlCancelamento($id)
    {
        $notaFiscal = NotaFiscal::findOrFail($id);

        if (!$notaFiscal->xml_cancelamento) {
            return back()->withErrors('XML de cancelamento não disponível.');
        }

        $filename = 'NFe_Cancelamento_' . ($notaFiscal->chave_acesso ?? $notaFiscal->id) . '.xml';

        return response($notaFiscal->xml_cancelamento, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
