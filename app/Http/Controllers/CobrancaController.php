<?php
namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\CobrancaEnviada;
use App\Services\LogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CobrancaController extends Controller
{
    // Exibe uma lista de cobranças
    public function index(Request $request)
    {
        $query = Cobranca::with(['venda.cliente']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('metodo_pagamento')) {
            $query->where('metodo_pagamento', $request->metodo_pagamento);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Busca por cliente ou venda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('venda.cliente', function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%");
            })->orWhere('id', 'like', "%{$search}%");
        }

        $cobrancas = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Estatísticas
        $stats = [
            'total' => Cobranca::count(),
            'pendentes' => Cobranca::where('status', 'pendente')->count(),
            'pagos' => Cobranca::where('status', 'pago')->count(),
            'cancelados' => Cobranca::where('status', 'cancelado')->count(),
            'valor_total' => Cobranca::where('status', 'pendente')->sum('valor'),
        ];

        return view('content.cobranca.listar', compact('cobrancas', 'stats'));
    }

    // Exibe o formulário para criar uma nova cobrança
    public function create()
    {
        $vendas = Venda::with('cliente')->whereDoesntHave('cobrancas', function($query) {
            $query->where('status', 'pendente');
        })->get();
        return view('content.cobranca.criar', compact('vendas'));
    }

    // Armazena uma nova cobrança no banco de dados
    public function store(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'metodo_pagamento' => 'required|in:pix,boleto,cartao_credito',
            'valor' => 'nullable|numeric|min:0',
            'data_vencimento' => 'nullable|date',
            'recorrente' => 'nullable|boolean',
            'frequencia_recorrencia' => 'nullable|in:1 month,3 months,6 months,1 year',
            'enviar_email' => 'nullable|boolean',
            'enviar_whatsapp' => 'nullable|boolean',
        ]);

        $venda = Venda::with('cliente')->findOrFail($request->venda_id);
        
        // Processa o valor formatado
        $valor = $venda->valor_total;
        if ($request->filled('valor')) {
            $valorStr = str_replace(['.', ','], ['', '.'], $request->valor);
            $valor = floatval($valorStr);
        }

        // Aplica prazo de vencimento padrão se configurado
        $prazoVencimentoPadrao = \App\Models\Configuracao::get('clientes_prazo_vencimento_padrao', '30');
        $dataVencimento = $request->data_vencimento ?? now()->addDays($prazoVencimentoPadrao);
        
        $cobranca = Cobranca::create([
            'venda_id' => $venda->id,
            'metodo_pagamento' => $request->metodo_pagamento,
            'valor' => $valor,
            'status' => 'pendente',
            'data_vencimento' => $dataVencimento,
            'recorrente' => $request->recorrente ?? false,
            'frequencia_recorrencia' => $request->frequencia_recorrencia,
            'proxima_cobranca' => $request->recorrente && $request->frequencia_recorrencia ? now()->add($request->frequencia_recorrencia) : null,
            'enviar_email' => $request->enviar_email ?? false,
            'enviar_whatsapp' => $request->enviar_whatsapp ?? false,
        ]);

        // Gera o PIX, boleto ou link de pagamento
        if ($request->metodo_pagamento === 'pix') {
            $cobranca->codigo_pix = $this->gerarPix($cobranca);
        } elseif ($request->metodo_pagamento === 'boleto') {
            $cobranca->link_boleto = $this->gerarBoleto($cobranca);
        } elseif ($request->metodo_pagamento === 'cartao_credito') {
            $cobranca->link_pagamento = $this->gerarLinkPagamento($cobranca);
        }

        $cobranca->save();

        // Envia a cobrança por e-mail ou WhatsApp, se necessário
        if ($request->enviar_email && $venda->cliente && $venda->cliente->email) {
            try {
                Mail::to($venda->cliente->email)->send(new CobrancaEnviada($cobranca));
            } catch (\Exception $e) {
                \Log::error('Erro ao enviar email de cobrança: ' . $e->getMessage());
            }
        }

        if ($request->enviar_whatsapp) {
            $this->enviarCobrancaPorWhatsapp($cobranca);
        }

        LogService::registrar(
            'Cobrança',
            'Criar',
            "Cobrança ID: {$cobranca->id} criada para venda ID: {$venda->id}"
        );

        return redirect()->route('cobrancas.index')->with('success', 'Cobrança criada com sucesso!');
    }

    // Exibe os detalhes de uma cobrança específica
    public function show($id)
    {
        $cobranca = Cobranca::with(['venda.cliente', 'venda.produtos'])->findOrFail($id);
        return view('content.cobranca.show', compact('cobranca'));
    }

    // Gera PDF da cobrança
    public function pdf($id)
    {
        $cobranca = Cobranca::with(['venda.cliente', 'venda.produtos'])->findOrFail($id);
        $pdf = Pdf::loadView('content.cobranca.pdf', compact('cobranca'));
        return $pdf->download("cobranca-{$cobranca->id}.pdf");
    }

    // Marca cobrança como paga
    public function marcarComoPaga($id)
    {
        $cobranca = Cobranca::findOrFail($id);
        $cobranca->update(['status' => 'pago']);

        LogService::registrar(
            'Cobrança',
            'Atualizar',
            "Cobrança ID: {$cobranca->id} marcada como paga"
        );

        return redirect()->back()->with('success', 'Cobrança marcada como paga!');
    }

    // Cancela cobrança
    public function cancelar($id)
    {
        $cobranca = Cobranca::findOrFail($id);
        $cobranca->update(['status' => 'cancelado']);

        LogService::registrar(
            'Cobrança',
            'Cancelar',
            "Cobrança ID: {$cobranca->id} cancelada"
        );

        return redirect()->back()->with('success', 'Cobrança cancelada!');
    }

    // Exibe o formulário para editar uma cobrança
    public function edit($id)
    {
        $cobranca = Cobranca::with('venda.cliente')->findOrFail($id);
        $vendas = Venda::with('cliente')->get();
        return view('content.cobranca.editar', compact('cobranca', 'vendas'));
    }

    // Atualiza uma cobrança no banco de dados
    public function update(Request $request, $id)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'metodo_pagamento' => 'required|in:pix,boleto,cartao_credito',
            'valor' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pendente,pago,cancelado',
            'data_vencimento' => 'nullable|date',
            'recorrente' => 'nullable|boolean',
            'frequencia_recorrencia' => 'nullable|in:1 month,3 months,6 months,1 year',
            'enviar_email' => 'nullable|boolean',
            'enviar_whatsapp' => 'nullable|boolean',
        ]);

        $cobranca = Cobranca::findOrFail($id);

        $updateData = [
            'venda_id' => $request->venda_id,
            'metodo_pagamento' => $request->metodo_pagamento,
            'recorrente' => $request->recorrente ?? false,
            'frequencia_recorrencia' => $request->frequencia_recorrencia,
            'proxima_cobranca' => $request->recorrente && $request->frequencia_recorrencia ? now()->add($request->frequencia_recorrencia) : null,
            'enviar_email' => $request->enviar_email ?? false,
            'enviar_whatsapp' => $request->enviar_whatsapp ?? false,
        ];

        if ($request->filled('valor')) {
            $valorStr = str_replace(['.', ','], ['', '.'], $request->valor);
            $updateData['valor'] = floatval($valorStr);
        }

        if ($request->filled('status')) {
            $updateData['status'] = $request->status;
        }

        if ($request->filled('data_vencimento')) {
            $updateData['data_vencimento'] = $request->data_vencimento;
        }

        $cobranca->update($updateData);

        LogService::registrar(
            'Cobrança',
            'Atualizar',
            "Cobrança ID: {$cobranca->id} atualizada"
        );

        return redirect()->route('cobrancas.index')->with('success', 'Cobrança atualizada com sucesso!');
    }

    // Remove uma cobrança do banco de dados
    public function destroy($id)
    {
        $cobranca = Cobranca::findOrFail($id);
        
        if ($cobranca->status === 'pago') {
            return redirect()->back()->with('error', 'Não é possível excluir uma cobrança já paga!');
        }

        $cobrancaId = $cobranca->id;
        $cobranca->delete();

        LogService::registrar(
            'Cobrança',
            'Excluir',
            "Cobrança ID: {$cobrancaId} excluída"
        );

        return redirect()->route('cobrancas.index')->with('success', 'Cobrança excluída com sucesso!');
    }

    // Métodos auxiliares
    private function gerarPix($cobranca)
    {
        // Gera código PIX estático (em produção, integrar com gateway de pagamento)
        $venda = $cobranca->venda;
        $chave = config('app.pix_chave', 'sua-chave-pix@exemplo.com');
        $valor = number_format($cobranca->valor, 2, '.', '');
        $descricao = "Cobrança #{$cobranca->id} - Venda #{$venda->id}";
        
        // Formato básico de código PIX (EMV)
        $pixCode = "00020126{$chave}52040000530398654{$valor}5802BR59{$descricao}6001S62070503***6304";
        
        return $pixCode;
    }

    private function gerarBoleto($cobranca)
    {
        // Gera link de boleto (em produção, integrar com gateway de pagamento)
        $venda = $cobranca->venda;
        $boletoId = 'BOL' . str_pad($cobranca->id, 10, '0', STR_PAD_LEFT);
        
        // Em produção, retornar URL real do gateway
        return route('cobrancas.boleto', ['id' => $cobranca->id]);
    }

    private function gerarLinkPagamento($cobranca)
    {
        // Gera link de pagamento (em produção, integrar com gateway de pagamento)
        return route('cobrancas.pagamento', ['id' => $cobranca->id]);
    }

    private function enviarCobrancaPorEmail($cobranca)
    {
        // Implementação já feita no método store
    }

    private function enviarCobrancaPorWhatsapp($cobranca)
    {
        // Em produção, integrar com API do WhatsApp Business
        // Por enquanto, apenas log
        \Log::info("Enviar cobrança por WhatsApp: {$cobranca->id}");
    }
}
