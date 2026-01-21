<?php

namespace App\Http\Controllers;

use App\Models\NotaFiscalServico;
use App\Models\Clientes;
use App\Services\NFSeService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class NotaFiscalServicoController extends Controller
{
    protected $nfseService;

    public function __construct(NFSeService $nfseService)
    {
        // Middleware 'auth' já é aplicado nas rotas em web.php
        $this->nfseService = $nfseService;
    }

    public function index()
    {
        $notas = NotaFiscalServico::with('cliente')->orderBy('created_at', 'desc')->paginate(20);
        return view('content.nfse.index', compact('notas'));
    }

    public function create()
    {
        $clientes = Clientes::all(); // Otimizar se tiver muitos clientes
        return view('content.nfse.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'valor_servico' => 'required|numeric|min:0',
            'discriminacao' => 'required|string',
            'iss_retido' => 'boolean',
            // Adicionar outras validações
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // Cálculos automáticos se não vierem do front
            $valorServico = $data['valor_servico'];
            $aliquotaIss = $data['aliquota_iss'] ?? 0;

            $valorIss = ($valorServico * $aliquotaIss) / 100;
            $valorTotal = $valorServico; // + impostos ou - retenções, ajustar lógica

            // Ajuste simples para o MVP
            $nfse = new NotaFiscalServico();
            $nfse->cliente_id = $data['cliente_id'];
            $nfse->user_id = Auth::id();
            $nfse->valor_servico = $valorServico;
            $nfse->valor_iss = $valorIss;
            $nfse->aliquota_iss = $aliquotaIss;
            $nfse->iss_retido = $request->has('iss_retido');
            $nfse->valor_total = $valorTotal;
            $nfse->discriminacao = $data['discriminacao'];
            $nfse->municipio_prestacao = $data['municipio_prestacao'] ?? null;
            $nfse->codigo_servico = $data['codigo_servico'] ?? null;
            $nfse->status = 'pendente';

            $nfse->save();

            DB::commit();

            LogService::registrar('NFS-e', 'Criar', "NFS-e criada para o cliente ID {$nfse->cliente_id}");

            // Se o usuário pediu para emitir imediatamente
            if ($request->has('emitir_agora')) {
                return $this->emitir($nfse->id);
            }

            return redirect()->route('nfse.index')->with('success', 'NFS-e salva com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::registrar('NFS-e', 'Erro', "Erro ao criar NFS-e: {$e->getMessage()}");
            return back()->withErrors('Erro ao salvar NFS-e: ' . $e->getMessage())->withInput();
        }
    }

    public function emitir($id)
    {
        $nfse = NotaFiscalServico::findOrFail($id);

        if ($nfse->status == 'autorizada') {
            return redirect()->route('nfse.index')->with('warning', 'NFS-e já está autorizada.');
        }

        $resultado = $this->nfseService->emitir($nfse);

        if ($resultado['status']) {
            LogService::registrar('NFS-e', 'Emitir', "NFS-e ID {$id} emitida com sucesso");
            return redirect()->route('nfse.index')->with('success', $resultado['message']);
        } else {
            LogService::registrar('NFS-e', 'Erro Emissão', "Erro ao emitir NFS-e ID {$id}: " . $resultado['message']);
            return redirect()->route('nfse.index')->with('error', $resultado['message']);
        }
    }

    public function show($id)
    {
        $nfse = NotaFiscalServico::with('cliente')->findOrFail($id);
        return view('content.nfse.show', compact('nfse'));
    }

    public function visualizarFake($chave)
    {
        // Busca a nota pela chave de acesso (simulada)
        $nfse = NotaFiscalServico::where('chave_acesso', $chave)->first();

        if (!$nfse) {
            return "NFS-e não encontrada para visualização simulada. (Chave: $chave)";
        }

        return view('content.nfse.visualizar-fake', compact('nfse'));
    }

    public function gerarPdf($id)
    {
        $nfse = NotaFiscalServico::with('cliente')->findOrFail($id);
        
        // Tenta baixar o PDF Oficial se a nota estiver autorizada
        if ($nfse->status == 'autorizada' && $nfse->chave_acesso) {
            try {
                $pdfContent = $this->nfseService->downloadPdf($nfse);
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', "inline; filename=NFSe_{$nfse->numero_nfse}.pdf");
            } catch (\Exception $e) {
                // Log::warning("Falha ao baixar PDF oficial: " . $e->getMessage());
                // Continua para o fallback
            }
        }
        
        // Renderiza a view existente para PDF (Fallback/Simulação)
        $pdf = Pdf::loadView('content.nfse.visualizar-fake', compact('nfse'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->stream("NFSe_{$nfse->numero_nfse}.pdf");
    }
}
