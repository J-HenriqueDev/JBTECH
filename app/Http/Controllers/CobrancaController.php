<?php
namespace App\Http\Controllers;

use App\Models\Cobranca;
use App\Models\Venda;
use Illuminate\Http\Request;

class CobrancaController extends Controller
{
    // Exibe uma lista de cobranças
    public function index()
    {
        $cobrancas = Cobranca::with('venda')->paginate(10);
        return view('content.cobranca.listar', compact('cobrancas'));
    }

    // Exibe o formulário para criar uma nova cobrança
    public function create()
    {
        $vendas = Venda::all();
        return view('content.cobranca.criar', compact('vendas'));
    }

    // Armazena uma nova cobrança no banco de dados
    public function store(Request $request)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'metodo_pagamento' => 'required|in:pix,boleto,cartao_credito',
            'recorrente' => 'nullable|boolean',
            'frequencia_recorrencia' => 'nullable|in:1 month,3 months,6 months,1 year',
            'enviar_email' => 'nullable|boolean',
            'enviar_whatsapp' => 'nullable|boolean',
        ]);

        $venda = Venda::findOrFail($request->venda_id);

        $cobranca = Cobranca::create([
            'venda_id' => $venda->id,
            'metodo_pagamento' => $request->metodo_pagamento,
            'valor' => $venda->valor_total,
            'status' => 'pendente',
            'data_vencimento' => $request->metodo_pagamento === 'boleto' ? now()->addDays(7) : null,
            'recorrente' => $request->recorrente ?? false,
            'frequencia_recorrencia' => $request->frequencia_recorrencia,
            'proxima_cobranca' => $request->recorrente ? now()->add($request->frequencia_recorrencia) : null,
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
        if ($request->enviar_email) {
            $this->enviarCobrancaPorEmail($cobranca);
        }

        if ($request->enviar_whatsapp) {
            $this->enviarCobrancaPorWhatsapp($cobranca);
        }

        return redirect()->route('cobrancas.index')->with('success', 'Cobrança criada com sucesso!');
    }

    // Exibe os detalhes de uma cobrança específica
    public function show($id)
    {
        $cobranca = Cobranca::with('venda')->findOrFail($id);
        return view('content.cobranca.pdf', compact('cobranca'));
    }

    // Exibe o formulário para editar uma cobrança
    public function edit($id)
    {
        $cobranca = Cobranca::findOrFail($id);
        $vendas = Venda::all();
        return view('content.cobranca.editar', compact('cobranca', 'vendas'));
    }

    // Atualiza uma cobrança no banco de dados
    public function update(Request $request, $id)
    {
        $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'metodo_pagamento' => 'required|in:pix,boleto,cartao_credito',
            'recorrente' => 'nullable|boolean',
            'frequencia_recorrencia' => 'nullable|in:1 month,3 months,6 months,1 year',
            'enviar_email' => 'nullable|boolean',
            'enviar_whatsapp' => 'nullable|boolean',
        ]);

        $cobranca = Cobranca::findOrFail($id);

        $cobranca->update([
            'venda_id' => $request->venda_id,
            'metodo_pagamento' => $request->metodo_pagamento,
            'recorrente' => $request->recorrente ?? false,
            'frequencia_recorrencia' => $request->frequencia_recorrencia,
            'proxima_cobranca' => $request->recorrente ? now()->add($request->frequencia_recorrencia) : null,
            'enviar_email' => $request->enviar_email ?? false,
            'enviar_whatsapp' => $request->enviar_whatsapp ?? false,
        ]);

        return redirect()->route('cobrancas.index')->with('success', 'Cobrança atualizada com sucesso!');
    }

    // Remove uma cobrança do banco de dados
    public function destroy($id)
    {
        $cobranca = Cobranca::findOrFail($id);
        $cobranca->delete();
        return redirect()->route('cobrancas.index')->with('success', 'Cobrança excluída com sucesso!');
    }

    // Métodos auxiliares
    private function gerarPix($cobranca)
    {
        // Lógica para gerar PIX
        return 'CODIGO_PIX_GERADO';
    }

    private function gerarBoleto($cobranca)
    {
        // Lógica para gerar boleto
        return 'LINK_BOLETO_GERADO';
    }

    private function gerarLinkPagamento($cobranca)
    {
        // Lógica para gerar link de pagamento
        return 'LINK_PAGAMENTO_GERADO';
    }

    private function enviarCobrancaPorEmail($cobranca)
    {
        // Lógica para enviar e-mail
    }

    private function enviarCobrancaPorWhatsapp($cobranca)
    {
        // Lógica para enviar WhatsApp
    }
}
