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
     * Exibe o formulário para emitir NF-e Avulsa (Sem venda vinculada)
     * Para casos de Perdas, Danos, Uso/Consumo, Complementar, Ajuste.
     */
    public function createAvulsa()
    {
        $produtos = \App\Models\Produto::select('id', 'nome', 'preco_venda', 'unidade_comercial as unidade', 'ncm')->get();
        // Incluindo UF na busca
        $clientes = \App\Models\Clientes::with('endereco')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'nome' => $c->nome,
                'cpf_cnpj' => $c->cpf_cnpj,
                'inscricao_estadual' => $c->inscricao_estadual,
                'uf' => $c->endereco->estado ?? '',
                'endereco' => $c->endereco
            ];
        });

        $fornecedores = \App\Models\Fornecedor::all();

        $naturezas = \App\Models\NaturezaOperacao::orderBy('descricao')->get();
        $emitenteUf = \App\Models\Configuracao::get('nfe_emitente_uf', 'SP'); // Padrão SP se não configurado

        // Dados do emitente para preenchimento automático quando "Próprio"
        $emitenteEndereco = [
            'logradouro' => \App\Models\Configuracao::get('nfe_endereco_logradouro', ''),
            'numero' => \App\Models\Configuracao::get('nfe_endereco_numero', ''),
            'bairro' => \App\Models\Configuracao::get('nfe_endereco_bairro', ''),
            'cidade' => \App\Models\Configuracao::get('nfe_endereco_municipio', ''),
            'uf' => \App\Models\Configuracao::get('nfe_endereco_uf', 'SP'),
            'cep' => \App\Models\Configuracao::get('nfe_cep', ''),
        ];

        LogService::registrar(
            'NF-e',
            'Criar Avulsa',
            'Acessou o formulário de emissão de NF-e Avulsa'
        );

        return view('content.nfe.create-avulsa', compact('produtos', 'clientes', 'fornecedores', 'naturezas', 'emitenteUf', 'emitenteEndereco'));
    }

    /**
     * Processa a criação da NF-e Avulsa
     */
    public function storeAvulsa(Request $request)
    {
        $request->validate([
            'natureza_operacao' => 'required|string',
            'tipo_documento' => 'required|in:0,1', // 0=Entrada, 1=Saída
            'destinatario_tipo' => 'required|in:proprio,cliente,fornecedor',
            'destinatario_id' => 'nullable|required_if:destinatario_tipo,cliente,fornecedor',

            // Validação de endereço manual (opcional, mas se vier, valida básicos)
            'destinatario_endereco_logradouro' => 'nullable|string',
            'destinatario_endereco_numero' => 'nullable|string',
            'destinatario_endereco_bairro' => 'nullable|string',
            'destinatario_endereco_cidade' => 'nullable|string',
            'destinatario_endereco_uf' => 'nullable|string|size:2',
            'destinatario_endereco_cep' => 'nullable|string',

            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'produtos.*.valor_unitario' => 'required|numeric|min:0.01',
            'produtos.*.cfop' => 'required|string|size:4',

            // Validação de Pagamento
            'pagamento.forma' => 'nullable|string',
            'pagamento.indicador' => 'nullable|in:0,1',
            'pagamento.dias' => 'nullable|integer|min:0',
            'pagamento.valor' => 'nullable|numeric|min:0',
            'pagamento.parcelas' => 'nullable|array|min:1',
            'pagamento.parcelas.*.data' => 'required_with:pagamento.parcelas|date',
            'pagamento.parcelas.*.valor' => 'required_with:pagamento.parcelas|numeric|min:0.01',
        ]);

        try {
            $notaFiscal = $this->nfeService->criarNotaAvulsa($request->all());

            LogService::registrar(
                'NF-e',
                'Criar Avulsa',
                "Criou rascunho de NF-e Avulsa ID: {$notaFiscal->id}"
            );

            return redirect()->route('nfe.edit', $notaFiscal->id)
                ->with('success', 'Rascunho da NF-e Avulsa criado! Verifique os dados antes de transmitir.');
        } catch (\Exception $e) {
            Log::error('Erro ao criar NF-e Avulsa: ' . $e->getMessage());
            return back()->withErrors('Erro ao criar rascunho: ' . $e->getMessage())->withInput();
        }
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
     * Emite uma NF-e (Cria rascunho e redireciona para edição)
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

            // Cria o rascunho da NF-e
            $notaFiscal = $this->nfeService->criarNotaDeVenda($venda);

            // Se retornar null, significa que todos os itens foram para NFS-e (ou não havia itens para NF-e)
            if (!$notaFiscal) {
                 return redirect()->route('vendas.index') // Ou redirecionar para lista de NFS-e se preferir
                    ->with('success', 'NFS-e gerada automaticamente! Nenhum item de mercadoria para emitir NF-e.');
            }

            LogService::registrar(
                'NF-e',
                'Criar Rascunho',
                "NF-e ID: {$notaFiscal->id} criada (rascunho) para venda ID: {$venda->id}"
            );

            // Redireciona para edição/conferência antes de transmitir
            return redirect()->route('nfe.edit', $notaFiscal->id)
                ->with('success', 'Rascunho da NF-e criado! Verifique os dados antes de transmitir.');
        } catch (\Exception $e) {
            Log::error('Erro ao criar rascunho de NF-e: ' . $e->getMessage());

            LogService::registrar(
                'NF-e',
                'Erro',
                "Erro ao criar rascunho de NF-e: {$e->getMessage()}"
            );

            return back()->withErrors('Erro ao criar rascunho de NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Exibe o formulário de edição da NF-e (Rascunho)
     */
    public function edit($id)
    {
        $notaFiscal = NotaFiscal::with(['venda.cliente', 'venda.produtos'])->findOrFail($id);

        $emitente = $this->nfeService->getDadosEmitente();

        // Carrega produtos para o modal de adicionar item
        $produtosDisponiveis = \App\Models\Produto::select('id', 'nome', 'preco_venda', 'unidade_comercial', 'ncm')->orderBy('nome')->get();

        // Se autorizada, a view edit.blade.php tratará como readonly

        return view('content.nfe.edit', compact('notaFiscal', 'emitente', 'produtosDisponiveis'));
    }

    /**
     * Atualiza os dados da NF-e (Rascunho)
     */
    public function update(Request $request, $id)
    {
        try {
            $notaFiscal = NotaFiscal::findOrFail($id);

            if ($notaFiscal->status == 'autorizada') {
                return back()->withErrors('Esta NF-e já foi autorizada e não pode ser editada.');
            }

            // Atualiza os dados JSON (Exemplo: Natureza da Operação, Produtos, etc)
            // Aqui você deve mapear os campos do formulário para a estrutura JSON

            $dadosDestinatario = $notaFiscal->dados_destinatario ?? [];
            if ($request->has('destinatario')) {
                Log::info('NFeController Update - Incoming destinatario:', $request->destinatario);
                $dadosDestinatario = array_merge($dadosDestinatario, $request->destinatario);
                Log::info('NFeController Update - Merged destinatario:', $dadosDestinatario);
            }

            $produtos = $notaFiscal->produtos;
            if ($request->has('produtos')) {
                // Atualiza produtos (CFOP, NCM, etc)
                foreach ($request->produtos as $index => $prodData) {
                    if (isset($produtos[$index])) {
                        $produtos[$index] = array_merge($produtos[$index], $prodData);
                    }
                }
            }

            // Recalcula o valor total da nota
            $valorTotal = 0;
            foreach ($produtos as $prod) {
                // Garante que é número para soma. Tenta pegar vProd ou valor_total
                $vProd = isset($prod['vProd']) ? (float)$prod['vProd'] : (isset($prod['valor_total']) ? (float)$prod['valor_total'] : 0);
                $valorTotal += $vProd;
            }

            // Atualiza observações se enviado
            $observacoes = $notaFiscal->observacoes;
            if ($request->has('infAdic') && isset($request->infAdic['infCpl'])) {
                $observacoes = $request->input('infAdic.infCpl');
            }

            // Atualiza data de saída se enviada
            $dataSaida = $notaFiscal->data_saida;
            if ($request->has('data_saida')) {
                $dataSaida = $request->data_saida ?: null;
            }

            // Atualiza dados de pagamento
            $dadosPagamento = $notaFiscal->dados_pagamento ?? [];
            if ($request->has('pagamento')) {
                $dadosPagamento = array_merge($dadosPagamento, $request->pagamento);
            }

            $notaFiscal->update([
                'dados_destinatario' => $dadosDestinatario,
                'produtos' => $produtos,
                'valor_total' => $valorTotal,
                'observacoes' => $observacoes,
                'data_saida' => $dataSaida,
                'dados_pagamento' => $dadosPagamento,
            ]);

            // Se o usuário clicou em "Salvar e Transmitir"
            if ($request->input('action') === 'transmitir') {
                return $this->transmitir($id);
            }

            return redirect()->route('nfe.edit', $notaFiscal->id)
                ->with('success', 'Dados da NF-e atualizados com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors('Erro ao atualizar NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Transmite a NF-e para a SEFAZ
     */
    public function transmitir($id)
    {
        try {
            $notaFiscal = NotaFiscal::findOrFail($id);

            if ($notaFiscal->status == 'autorizada') {
                return redirect()->route('nfe.show', $notaFiscal->id)
                    ->with('info', 'Esta NF-e já foi autorizada.');
            }

            $this->nfeService->transmitirNFe($notaFiscal);

            LogService::registrar(
                'NF-e',
                'Transmitir',
                "NF-e ID: {$notaFiscal->id} transmitida."
            );

            if ($notaFiscal->status == 'autorizada') {
                return redirect()->route('nfe.show', $notaFiscal->id)
                    ->with('success', 'NF-e autorizada com sucesso!');
            } else {
                return redirect()->route('nfe.show', $notaFiscal->id)
                    ->withErrors('NF-e rejeitada ou em processamento. Verifique o status.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao transmitir NF-e: ' . $e->getMessage());
            return back()->withErrors('Erro ao transmitir NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de uma NF-e
     */
    public function show($id)
    {
        $notaFiscal = NotaFiscal::with(['venda.cliente.endereco', 'venda.produtos', 'cliente.endereco'])
            ->findOrFail($id);

        // LogService::registrar(
        //    'NF-e',
        //    'Visualizar',
        //    "Visualizou a NF-e ID: {$notaFiscal->id}"
        // );

        // Redireciona para a tela unificada de Ver/Editar
        return redirect()->route('nfe.edit', $id);
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
     * Exibe o XML da NF-e no navegador
     */
    public function viewXml($id)
    {
        $notaFiscal = NotaFiscal::findOrFail($id);

        if (!$notaFiscal->xml) {
            return back()->withErrors('XML da NF-e não disponível.');
        }

        return response($notaFiscal->xml, 200)
            ->header('Content-Type', 'application/xml');
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

    /**
     * Envia carta de correção
     */
    public function cartaCorrecao(Request $request, $id)
    {
        $request->validate(['texto_correcao' => 'required|string|min:15']);

        try {
            $notaFiscal = NotaFiscal::findOrFail($id);
            $this->nfeService->cartaCorrecao($notaFiscal, $request->texto_correcao);

            LogService::registrar('NF-e', 'CC-e', 'Enviou carta de correção para NF-e #' . $notaFiscal->numero_nfe);

            return back()->with('success', 'Carta de Correção enviada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors('Erro ao enviar CC-e: ' . $e->getMessage());
        }
    }

    /**
     * Inutiliza numeração
     */
    public function inutilizar(Request $request)
    {
        $request->validate([
            'serie' => 'required|integer',
            'numero_inicial' => 'required|integer',
            'numero_final' => 'required|integer',
            'justificativa' => 'required|string|min:15',
        ]);

        try {
            $this->nfeService->inutilizar(
                $request->serie,
                $request->numero_inicial,
                $request->numero_final,
                $request->justificativa
            );

            LogService::registrar('NF-e', 'Inutilizar', "Inutilizou numeração: Série {$request->serie}, {$request->numero_inicial} a {$request->numero_final}");

            return back()->with('success', 'Numeração inutilizada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors('Erro ao inutilizar numeração: ' . $e->getMessage());
        }
    }

    /**
     * Envia NF-e por email
     */
    public function enviarEmail(Request $request, $id)
    {
        try {
            $notaFiscal = NotaFiscal::findOrFail($id);
            $email = $request->email ?? $notaFiscal->cliente->email;

            if (!$email) {
                return back()->withErrors('Email não informado e não encontrado no cadastro do cliente.');
            }

            $this->nfeService->enviarEmail($notaFiscal, $email);

            LogService::registrar('NF-e', 'Email', 'Enviou NF-e #' . $notaFiscal->numero_nfe . ' por email para ' . $email);

            return back()->with('success', 'Email enviado com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors('Erro ao enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Gera DANFE (PDF)
     */
    public function gerarDanfe($id)
    {
        try {
            $notaFiscal = NotaFiscal::findOrFail($id);
            $pdfContent = $this->nfeService->gerarPdf($notaFiscal);

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="NFe_' . $notaFiscal->chave_acesso . '.pdf"');
        } catch (\Exception $e) {
            return back()->withErrors('Erro ao gerar DANFE: ' . $e->getMessage());
        }
    }

    /**
     * Remove a NF-e do banco de dados
     */
    public function destroy($id)
    {
        $notaFiscal = NotaFiscal::findOrFail($id);

        if ($notaFiscal->status == 'autorizada') {
            return back()->withErrors('Não é possível excluir uma NF-e autorizada. Use o cancelamento.');
        }

        $notaFiscal->delete();

        return redirect()->route('nfe.index')->with('success', 'NF-e excluída com sucesso!');
    }
}
