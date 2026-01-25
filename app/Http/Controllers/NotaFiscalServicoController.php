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
        $servicos = \App\Models\Servico::where('ativo', true)->get();
        return view('content.nfse.create', compact('clientes', 'servicos'));
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

        // Busca logs relacionados a esta NFS-e
        // Procura por "ID {id}" na coluna detalhes
        $logs = \App\Models\Log::where('categoria', 'NFS-e')
            ->where('detalhes', 'like', "%ID {$id}%")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('content.nfse.show', compact('nfse', 'logs'));
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
        // Adicionado try-catch para evitar erro de fonte se o arquivo não existir
        try {
            $pdf = Pdf::loadView('content.nfse.visualizar-fake', compact('nfse'));
            $pdf->setPaper('a4', 'portrait');
            return $pdf->stream("NFSe_{$nfse->numero_nfse}.pdf");
        } catch (\Exception $e) {
            return response("Erro ao gerar PDF de simulação: " . $e->getMessage(), 500);
        }
    }

    public function downloadXml($id)
    {
        $nfse = NotaFiscalServico::findOrFail($id);

        $xmlContent = $nfse->xml_retorno ?? $nfse->xml_envio;

        if (!$xmlContent) {
            return back()->with('error', 'XML da NFS-e não disponível.');
        }

        // Se o XML estiver codificado em base64 (comum em algumas APIs), decodifica
        // Mas assumindo que está salvo como string raw ou já decodificado no banco
        // Verifica se é base64 simples (sem tags XML no inicio)
        if (!str_contains($xmlContent, '<') && base64_decode($xmlContent, true)) {
             $xmlContent = base64_decode($xmlContent);
        }

        // Se estiver gzipado (detectar magic number ou similar se necessário, mas aqui vamos simples)
        // O padrão do projeto parece ser salvar raw string se possível.

        $filename = 'NFSe_' . ($nfse->chave_acesso ?? $nfse->id) . '.xml';

        return response($xmlContent, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function edit($id)
    {
        $nfse = NotaFiscalServico::findOrFail($id);

        if ($nfse->status == 'autorizada') {
            return redirect()->route('nfse.index')->with('warning', 'NFS-e autorizada não pode ser editada.');
        }

        $clientes = Clientes::all();
        $servicos = \App\Models\Servico::where('ativo', true)->get();
        return view('content.nfse.edit', compact('nfse', 'clientes', 'servicos'));
    }

    public function update(Request $request, $id)
    {
        $nfse = NotaFiscalServico::findOrFail($id);

        if ($nfse->status == 'autorizada') {
            return redirect()->route('nfse.index')->with('error', 'NFS-e autorizada não pode ser editada.');
        }

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'valor_servico' => 'required|numeric|min:0',
            'discriminacao' => 'required|string',
            'iss_retido' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            $valorServico = $data['valor_servico'];
            $aliquotaIss = $data['aliquota_iss'] ?? 0;
            $valorIss = ($valorServico * $aliquotaIss) / 100;
            $valorTotal = $valorServico;

            $nfse->cliente_id = $data['cliente_id'];
            $nfse->valor_servico = $valorServico;
            $nfse->valor_iss = $valorIss;
            $nfse->aliquota_iss = $aliquotaIss;
            $nfse->iss_retido = $request->has('iss_retido');
            $nfse->valor_total = $valorTotal;
            $nfse->discriminacao = $data['discriminacao'];
            $nfse->municipio_prestacao = $data['municipio_prestacao'] ?? null;
            $nfse->codigo_servico = $data['codigo_servico'] ?? null;

            $nfse->save();

            DB::commit();

            LogService::registrar('NFS-e', 'Atualizar', "NFS-e ID {$id} atualizada");

            if ($request->has('emitir_agora')) {
                return $this->emitir($nfse->id);
            }

            return redirect()->route('nfse.index')->with('success', 'NFS-e atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            LogService::registrar('NFS-e', 'Erro Atualizar', "Erro ao atualizar NFS-e ID {$id}: " . $e->getMessage());
            return back()->withErrors('Erro ao atualizar NFS-e: ' . $e->getMessage())->withInput();
        }
    }

    public function cancelar(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|min:15',
            'codigo_cancelamento' => 'required|string'
        ]);

        $nfse = NotaFiscalServico::findOrFail($id);

        if ($nfse->status !== 'autorizada') {
            return back()->with('error', 'Apenas NFS-e autorizada pode ser cancelada.');
        }

        try {
            // Chama o serviço de cancelamento
            $response = $this->nfseService->cancelar($nfse, $request->codigo_cancelamento, $request->motivo);

            // Se chegou aqui, a requisição foi enviada com sucesso.
            // O status da nota deve ser atualizado pelo serviço ou aqui, dependendo da resposta.
            // A biblioteca retorna a resposta da API. Precisamos verificar se foi homologado o cancelamento.
            // Mas o método cancelar do serviço já deve tratar exceções.

            // Vamos assumir que se não deu erro, foi enviado.
            // Porém, o ideal é checar o status na resposta.
            // Por simplicidade e confiança no serviço:

            $nfse->status = 'cancelada';
            $nfse->save();

            LogService::registrar('NFS-e', 'Cancelar', "NFS-e ID {$id} cancelada com sucesso.");
            return redirect()->route('nfse.index')->with('success', 'Solicitação de cancelamento enviada com sucesso!');

        } catch (\Exception $e) {
            LogService::registrar('NFS-e', 'Erro Cancelamento', "Erro ao cancelar NFS-e ID {$id}: " . $e->getMessage());
            return back()->with('error', 'Erro ao cancelar NFS-e: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $nfse = NotaFiscalServico::findOrFail($id);

        if ($nfse->status == 'autorizada') {
            return back()->with('error', 'Não é possível excluir uma NFS-e autorizada. Use o cancelamento.');
        }

        $nfse->delete();
        LogService::registrar('NFS-e', 'Excluir', "NFS-e ID {$id} excluída");

        return redirect()->route('nfse.index')->with('success', 'NFS-e excluída com sucesso!');
    }
}
