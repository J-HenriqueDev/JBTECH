<?php

namespace App\Http\Controllers;

use App\Models\NotaEntrada;
use App\Models\Fornecedor;
use App\Models\ContaPagar;
use App\Models\Produto;
use App\Services\NFeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\LogService;
use Illuminate\Support\Facades\DB;
use App\Services\CustomDanfe;
use Illuminate\Support\Facades\Response;

class NotaEntradaController extends Controller
{
    protected $nfeService;

    public function __construct(NFeService $nfeService)
    {
        $this->nfeService = $nfeService;
    }

    public function index()
    {
        $notas = NotaEntrada::orderBy('data_emissao', 'desc')->paginate(10);

        // Obtém dados centralizados de saúde fiscal
        $healthStats = SefazHealthController::getHealthData();

        // Mantém compatibilidade com a variável $bloqueioMsg usada no botão de busca
        $bloqueioMsg = $healthStats['sonecaMinutos'] > 0 ? $healthStats['statusMessage'] : null;

        return view('content.notas-entrada.index', compact('notas', 'bloqueioMsg', 'healthStats'));
    }

    public function buscarNovas()
    {
        try {
            // Verifica se existe bloqueio de tempo para nova consulta (Consumo Indevido ou Sem Documentos)
            $nextQuery = \App\Models\Configuracao::get('nfe_next_dfe_query');
            if ($nextQuery) {
                $nextQueryDate = \Carbon\Carbon::parse($nextQuery);
                if (now()->lt($nextQueryDate)) {
                    $diffMinutes = (int) ceil(now()->diffInMinutes($nextQueryDate));
                    return redirect()->back()->with('error', "Aguarde {$diffMinutes} minutos para realizar uma nova busca (Regra da SEFAZ para evitar bloqueio).");
                }
            }

            // Verifica intervalo de espera por consulta vazia (137)
            $nextNSUCheck = \App\Models\Configuracao::get('nfe_next_nsu_check');
            if ($nextNSUCheck) {
                $nextNSUCheckDate = \Carbon\Carbon::parse($nextNSUCheck);
                if (now()->lt($nextNSUCheckDate)) {
                    $diffMinutes = (int) ceil(now()->diffInMinutes($nextNSUCheckDate));
                    return redirect()->back()->with('warning', "Aguarde {$diffMinutes} minutos para nova busca (Intervalo obrigatório SEFAZ após resposta vazia).");
                }
            }

            $lastNSU = \App\Models\Configuracao::get('nfe_last_nsu') ?: 0;

            // Limite de loops para evitar timeout (máximo 10 páginas ou 500 documentos)
            $maxLoops = 10;
            $loopCount = 0;
            $newDocsCount = 0;

            do {
                $resp = $this->nfeService->consultarNotasDestinadas($lastNSU);

                $ultNSU = $resp->ultNSU;
                $maxNSU = $resp->maxNSU;

                if (isset($resp->loteDistDFeInt->docZip)) {
                    $docs = is_array($resp->loteDistDFeInt->docZip) ? $resp->loteDistDFeInt->docZip : [$resp->loteDistDFeInt->docZip];

                    foreach ($docs as $doc) {
                        try {
                            $nsu = null;
                            $schema = null;
                            $contentEncoded = null;

                            if (is_object($doc)) {
                                $nsu = $doc->NSU ?? null;
                                $schema = $doc->schema ?? null;
                                $contentEncoded = $doc->{'$'} ?? $doc->{0} ?? null;
                                // Fallback
                                if (!$contentEncoded && method_exists($doc, '__toString')) {
                                    $str = (string)$doc;
                                    if (strlen($str) > 20) $contentEncoded = $str;
                                }
                            } elseif (is_array($doc)) {
                                $nsu = $doc['NSU'] ?? null;
                                $schema = $doc['schema'] ?? null;
                                $contentEncoded = $doc['$'] ?? ($doc[0] ?? null);
                            } elseif (is_string($doc)) {
                                $contentEncoded = $doc;
                            }

                            if (!$contentEncoded && (is_object($doc) || is_array($doc))) {
                                foreach ($doc as $key => $value) {
                                    if (is_string($value) && strlen($value) > 20) {
                                        $contentEncoded = $value;
                                        break;
                                    }
                                }
                            }

                            if ($contentEncoded) {
                                $xmlContent = gzdecode(base64_decode($contentEncoded));
                                $resultType = $this->processarDocDFe($nsu, $schema, $xmlContent);

                                if ($resultType === 'new') {
                                    $newDocsCount++;
                                }
                            }
                        } catch (\Exception $eDoc) {
                            Log::error("NotaEntradaController: Erro ao processar documento individual (NSU " . ($nsu ?? 'N/A') . "): " . $eDoc->getMessage());
                        }
                    }
                }

                // Atualiza o último NSU consultado
                \App\Models\Configuracao::set('nfe_last_nsu', $ultNSU, 'nfe', 'text', 'Último NSU consultado na SEFAZ');

                $lastNSU = $ultNSU;
                $loopCount++;

                // Se ultNSU == maxNSU, chegamos ao fim
                if ($ultNSU >= $maxNSU) {
                    break;
                }

                // Rate limiting simples para evitar Consumo Indevido (656) em loops rápidos
                sleep(2);
            } while ($loopCount < $maxLoops);

            $msgSuccess = "Busca realizada!";
            if ($newDocsCount > 0) {
                $msgSuccess .= " {$newDocsCount} novos documentos encontrados.";
            } else {
                $msgSuccess .= " Nenhum documento novo encontrado.";
            }

            return redirect()->back()->with('success', $msgSuccess);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, '656') || str_contains($msg, 'Consumo Indevido')) {
                $msg = 'A SEFAZ bloqueou temporariamente as consultas por excesso de tentativas (Consumo Indevido). Por favor, aguarde 1 hora antes de tentar novamente.';
            }

            Log::error('Erro ao buscar notas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao buscar notas: ' . $msg);
        }
    }

    protected function processarDocDFe($nsu, $schema, $xmlContent)
    {
        $xml = simplexml_load_string($xmlContent);

        if (!$schema || $schema === '') {
            $content = (string) $xmlContent;
            if (strpos($content, '<resNFe') !== false) {
                $schema = 'resNFe_v1.00.xsd';
            } elseif (strpos($content, '<resEvento') !== false) {
                $schema = 'resEvento_v1.00.xsd';
            } else {
                $schema = 'procNFe_v4.00.xsd';
            }
        }

        if (strpos($schema, 'resNFe') !== false) {
            // Resumo da NFe
            $chave = preg_replace('/[^0-9]/', '', (string) $xml->chNFe);
            $cnpj = (string) $xml->CNPJ; // CNPJ do Emitente
            $nome = (string) $xml->xNome;
            $valor = (float) $xml->vNF;
            $statusSefaz = (int) $xml->cSitNFe; // 1=Autorizada, 2=Denegada, 3=Cancelada
            $data = (string) $xml->dhEmi;

            $status = 'detectada';
            if ($statusSefaz == 3) $status = 'cancelada';

            // Verifica se a nota já existe para preservar status avançado
            $notaExistente = NotaEntrada::where('chave_acesso', $chave)->first();

            // Se já temos o XML baixado, não regredimos o status para detectada
            if ($notaExistente && !empty($notaExistente->xml_content)) {
                $status = $notaExistente->status;
            }

            $nota = NotaEntrada::updateOrCreate(
                ['chave_acesso' => $chave],
                [
                    'emitente_cnpj' => $cnpj,
                    'emitente_nome' => $nome,
                    'valor_total' => $valor,
                    'data_emissao' => $data,
                    'status' => $status
                ]
            );
            return $nota->wasRecentlyCreated ? 'new' : 'updated';
        } elseif (strpos($schema, 'procNFe') !== false || strpos($schema, 'resNFe') === false) {
            // NFe Completa (XML)
            // Tenta localizar o infNFe independentemente da estrutura (procNFe ou NFe direta)
            $infNFe = null;
            if (isset($xml->NFe->infNFe)) {
                $infNFe = $xml->NFe->infNFe;
            } elseif (isset($xml->infNFe)) {
                $infNFe = $xml->infNFe;
            }

            if ($infNFe) {
                $chave = preg_replace('/[^0-9]/', '', (string) $infNFe['Id']);
                $emit = $infNFe->emit;
                $total = $infNFe->total->ICMSTot;

                $nota = NotaEntrada::updateOrCreate(
                    ['chave_acesso' => $chave],
                    [
                        'emitente_cnpj' => (string) $emit->CNPJ,
                        'emitente_nome' => (string) $emit->xNome,
                        'numero_nfe' => (string) $infNFe->ide->nNF,
                        'serie' => (string) $infNFe->ide->serie,
                        'valor_total' => (float) $total->vNF,
                        'data_emissao' => (string) $infNFe->ide->dhEmi,
                        'xml_content' => $xmlContent,
                        'status' => 'concluido'
                    ]
                );
                return $nota->wasRecentlyCreated ? 'new' : 'updated';
            }
        } elseif (strpos($schema, 'resEvento') !== false) {
            // Evento (ex: Cancelamento)
            $chave = (string) $xml->chNFe;
            $tpEvento = (string) $xml->tpEvento;

            // 110111 = Cancelamento
            if ($tpEvento == '110111') {
                NotaEntrada::where('chave_acesso', $chave)->update(['status' => 'cancelada']);
                return 'updated';
            } else {
                Log::info("NotaEntradaController: Evento ignorado (NSU: $nsu, Schema: $schema, Tipo: $tpEvento, Chave: $chave)");
            }
        } else {
            Log::info("NotaEntradaController: Schema desconhecido ou ignorado (NSU: $nsu, Schema: $schema)");
        }
        return 'ignored';
    }

    public function baixarPorChave(Request $request)
    {
        // Log::info('Iniciando baixarPorChave', ['request_all' => $request->all()]);

        // Sanitiza a chave para remover espaços e caracteres não numéricos
        $chaveOriginal = $request->input('chave');
        $chave = preg_replace('/[^0-9]/', '', $chaveOriginal);

        // Log::info('Chave sanitizada', ['original' => $chaveOriginal, 'sanitizada' => $chave, 'tamanho' => strlen($chave)]);

        $request->merge(['chave' => $chave]);

        $request->validate(['chave' => 'required|size:44']);

        // $chave já está sanitizada no request, mas vamos usar a variável local para garantir


        // Verifica se já temos a nota
        $nota = NotaEntrada::where('chave_acesso', $chave)->first();

        if ($nota && $nota->xml_content) {
            return redirect()->route('notas-entrada.processar', $nota->id)
                ->with('success', 'Nota localizada! Pronto para dar entrada.');
        }

        try {
            $result = $this->nfeService->baixarPorChave($chave);

            if (isset($result['status']) && $result['status'] === 'error') {
                // Se o erro for de documento não encontrado ou vazio
                if (
                    str_contains($result['message'], 'Nenhum XML') ||
                    str_contains($result['message'], 'não localizado') ||
                    str_contains($result['message'], 'Erro SEFAZ')
                ) {
                    // FEEDBACK DINÂMICO E CRIAÇÃO MANUAL
                    // Se não existe, criamos para o robô processar
                    if (!$nota) {
                        NotaEntrada::create([
                            'chave_acesso' => $chave,
                            'status' => 'detectada',
                            'manifestacao' => 'sem_manifestacao',
                            'user_id' => auth()->id() // Rastreabilidade
                        ]);

                        $minutos = \App\Models\Configuracao::getTempoRestanteSoneca();
                        $tempoEstimado = $minutos > 0 ? $minutos : 'alguns';

                        return redirect()->back()->with('warning', "Nota agendada para download. Devido aos limites do Governo, o robô processará este documento em aproximadamente {$tempoEstimado} minutos.");
                    }

                    return redirect()->back()->with('warning', 'XML ainda não disponível na SEFAZ. Tente novamente em alguns minutos.');
                }

                throw new \Exception($result['message']);
            }

            $this->processarDocDFe($result['nsu'], $result['schema'], $result['content']);

            // Tenta recuperar a nota processada
            $nota = NotaEntrada::where('chave_acesso', $chave)->first();

            if ($nota) {
                if ($nota->xml_content) {
                    // Alterado para redirect back conforme solicitação de "refresh e botão verde"
                    return redirect()->back()
                        ->with('success', 'Nota fiscal baixada com sucesso! Clique em "Dar Entrada".');
                } else {
                    return redirect()->route('nfe.manifesto.index')
                        ->with('success', 'Resumo da nota baixado com sucesso! A nota foi adicionada ao Manifesto. Realize a ciência/confirmação para baixar o XML completo.');
                }
            }

            return redirect()->back()->with('success', "Nota baixada (Resumo). Aguarde a liberação do XML completo.");
        } catch (\Exception $e) {
            Log::error("Erro ao baixar nota por chave ($chave): " . $e->getMessage());

            // Tratamento especial para mensagem de sucesso com delay (Confirmação da Operação ou Ciência)
            if (
                str_contains($e->getMessage(), "Confirmação da Operação' foi realizada com sucesso") ||
                str_contains($e->getMessage(), "Ciência da Operação")
            ) {

                // Garante que a nota esteja salva para o robô tentar baixar novamente
                NotaEntrada::updateOrCreate(
                    ['chave_acesso' => $chave],
                    [
                        'status' => 'detectada' // Força status para o cron job processar
                    ]
                );

                return redirect()->route('nfe.manifesto.index')->with('warning', $e->getMessage() . " A nota foi registrada e o XML será baixado automaticamente assim que liberado pela SEFAZ.");
            }

            return redirect()->back()->with('error', 'Erro ao baixar nota: ' . $e->getMessage());
        }
    }

    public function uploadXml(Request $request)
    {
        $request->validate(['xml_file' => 'required|file|mimes:xml']);

        try {
            $xmlContent = file_get_contents($request->file('xml_file')->getRealPath());
            $xml = simplexml_load_string($xmlContent);

            if (!$xml || !isset($xml->NFe)) {
                throw new \Exception('Arquivo XML inválido ou não é uma NF-e.');
            }

            $nfe = $xml->NFe->infNFe;
            $chave = str_replace('NFe', '', (string) $nfe['Id']);

            $nota = NotaEntrada::updateOrCreate(
                ['chave_acesso' => $chave],
                [
                    'emitente_cnpj' => (string) $nfe->emit->CNPJ,
                    'emitente_nome' => (string) $nfe->emit->xNome,
                    'valor_total' => (float) $nfe->total->ICMSTot->vNF,
                    'data_emissao' => (string) $nfe->ide->dhEmi,
                    'xml_content' => $xmlContent,
                    'status' => 'concluido'
                ]
            );

            return redirect()->route('notas-entrada.processar', $nota->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao importar XML: ' . $e->getMessage());
        }
    }

    public function processar($id)
    {
        $nota = NotaEntrada::findOrFail($id);

        // Trava de duplicidade: se já estiver processada, redireciona para a visualização (Espelho)
        if ($nota->status === 'processada') {
            return redirect()->route('admin.fiscal.espelho', $nota->id)
                ->with('warning', 'Esta nota já foi processada. Redirecionado para a visualização da importação.');
        }

        if (!$nota->xml_content) {
            return redirect()->route('notas-entrada.index')->with('error', 'Nota sem XML para processar.');
        }

        $xml = simplexml_load_string($nota->xml_content);
        $itens = [];
        $duplicatas = [];

        // Verifica a estrutura do XML (NFe direta ou procNFe)
        $infNFe = null;
        if (isset($xml->NFe->infNFe)) {
            $infNFe = $xml->NFe->infNFe;
        } elseif (isset($xml->infNFe)) {
            $infNFe = $xml->infNFe;
        } else {
            // Tenta encontrar em namespaces se necessário, mas geralmente um desses dois funciona
            // Se falhar, talvez seja um resumo ou evento
            return redirect()->route('notas-entrada.index')->with('error', 'Estrutura do XML inválida para processamento de itens.');
        }

        // Captura duplicatas para exibição na conferência
        if (isset($infNFe->cobr) && isset($infNFe->cobr->dup)) {
            foreach ($infNFe->cobr->dup as $dup) {
                $duplicatas[] = [
                    'nDup' => (string) ($dup->nDup ?? ''),
                    'vDup' => (float) ($dup->vDup ?? 0),
                    'dVenc' => (string) ($dup->dVenc ?? '')
                ];
            }
        }

        foreach ($infNFe->det as $det) {
            $prod = $det->prod;

            // Tenta encontrar produto por código de barras
            $produtoExistente = \App\Models\Produto::where('codigo_barras', (string) $prod->cEAN)
                ->orWhere('codigo_barras', (string) $prod->cEANTrib)
                ->first();

            $ultimoCusto = 0;
            if ($produtoExistente) {
                $ultimoCusto = $produtoExistente->preco_custo;
            }

            $itens[] = [
                'nItem' => (int) $det['nItem'],
                'cProd' => (string) $prod->cProd,
                'xProd' => (string) $prod->xProd,
                'NCM' => (string) $prod->NCM,
                'CEST' => (string) ($prod->CEST ?? ''),
                'CFOP' => (string) $prod->CFOP,
                'uCom' => (string) $prod->uCom,
                'qCom' => (float) $prod->qCom,
                'vUnCom' => (float) $prod->vUnCom,
                'vProd' => (float) $prod->vProd,
                'cEAN' => (string) $prod->cEAN,
                'cEANTrib' => (string) $prod->cEANTrib,
                'produto_existente' => $produtoExistente,
                'ultimo_custo' => $ultimoCusto
            ];
        }

        return view('content.notas-entrada.processar', compact('nota', 'itens', 'duplicatas'));
    }

    public function confirmarProcessamento(Request $request, $id)
    {
        $nota = NotaEntrada::findOrFail($id);

        if ($nota->status === 'processada') {
            return redirect()->route('notas-entrada.index')->with('warning', 'Esta nota já foi processada anteriormente e o estoque já foi atualizado.');
        }

        try {
            DB::beginTransaction();

            // 1. Processar XML para obter dados do Fornecedor e Financeiro
            $xml = simplexml_load_string($nota->xml_content);

            // Tratamento de Namespaces para garantir acesso
            $infNFe = isset($xml->NFe->infNFe) ? $xml->NFe->infNFe : ($xml->infNFe ?? null);

            if (!$infNFe) {
                // Tenta namespace padrão se falhar
                $ns = $xml->getNamespaces(true);
                $xml->registerXPathNamespace('n', $ns[''] ?? 'http://www.portalfiscal.inf.br/nfe');
                $infNFe = $xml->xpath('//n:infNFe')[0] ?? null;
            }

            if (!$infNFe) {
                throw new \Exception("Estrutura do XML inválida: infNFe não encontrado.");
            }

            // --- GESTÃO DE FORNECEDOR ---
            $emit = $infNFe->emit;
            $cnpj = (string) $emit->CNPJ;
            $nome = (string) $emit->xNome;
            $ender = $emit->enderEmit;

            $fornecedor = Fornecedor::updateOrCreate(
                ['cnpj' => $cnpj],
                [
                    'nome' => $nome,
                    'telefone' => (string) ($ender->fone ?? ''),
                    'email' => null, // XML geralmente não traz email
                    'endereco' => (string) ($ender->xLgr ?? ''),
                    'numero' => (string) ($ender->nro ?? ''),
                    'bairro' => (string) ($ender->xBairro ?? ''),
                    'cidade' => (string) ($ender->xMun ?? ''),
                    'uf' => (string) ($ender->UF ?? ''),
                    'cep' => (string) ($ender->CEP ?? ''),
                ]
            );

            // --- GESTÃO FINANCEIRA (CONTAS A PAGAR) ---
            // Verifica se já existem contas para esta nota para evitar duplicidade em reprocessamento
            if (!ContaPagar::where('nota_entrada_id', $nota->id)->exists()) {
                $cobr = $infNFe->cobr ?? null;

                if ($cobr && isset($cobr->dup)) {
                    // Tem duplicatas (Parcelado)
                    foreach ($cobr->dup as $dup) {
                        ContaPagar::create([
                            'fornecedor_id' => $fornecedor->id,
                            'nota_entrada_id' => $nota->id,
                            'descricao' => "NF-e " . ($nota->numero_nfe ?? $nota->chave_acesso) . " - Parc " . (string) $dup->nDup,
                            'valor' => (float) $dup->vDup,
                            'data_vencimento' => (string) $dup->dVenc,
                            'status' => 'pendente',
                            'origem' => 'importacao_nfe',
                            'numero_documento' => (string) $dup->nDup
                        ]);
                    }
                } else {
                    // Pagamento à vista ou sem duplicata explícita
                    // Verifica forma de pagamento
                    $pag = $infNFe->pag ?? null;
                    $status = 'pendente';
                    $dataVencimento = now()->toDateString();
                    $obs = 'Importado via XML (Sem duplicatas)';

                    // Se pagamento for dinheiro (01) ou cartão (03, 04), poderia marcar como pago?
                    // Por segurança, deixamos pendente para conferência, ou pago se for Dinheiro.
                    // Vamos manter pendente para o usuário dar baixa no caixa/banco.

                    ContaPagar::create([
                        'fornecedor_id' => $fornecedor->id,
                        'nota_entrada_id' => $nota->id,
                        'descricao' => "NF-e " . ($nota->numero_nfe ?? $nota->chave_acesso) . " - Pagamento Único",
                        'valor' => (float) ($infNFe->total->ICMSTot->vNF ?? 0),
                        'data_vencimento' => $dataVencimento,
                        'status' => 'pendente',
                        'origem' => 'importacao_nfe',
                        'observacoes' => $obs
                    ]);
                }
            }

            $categorizer = new \App\Services\CategorizerService();

            foreach ($request->itens as $item) {
                // Ação 'ignorar' foi removida, mas mantemos verificação de segurança
                $acao = $item['acao'] ?? 'criar';

                if ($acao === 'ignorar') {
                    continue;
                }

                $quantidade = (float) $item['quantidade'];
                $precoCusto = (float) $item['preco_custo'];
                $precoVenda = (float) $item['preco_venda'];

                // Atualização de estoque é OBRIGATÓRIA na entrada
                $atualizarEstoque = true;

                $produto = null;

                if (($acao === 'atualizar' || $acao === 'associar') && !empty($item['produto_id'])) {
                    $produto = \App\Models\Produto::find($item['produto_id']);
                } elseif ($acao === 'criar') {
                    $produto = new \App\Models\Produto();
                    $produto->nome = $item['nome_novo'] ?? $item['xProd'];
                    $produto->codigo_barras = ($item['cEAN'] !== 'SEM GTIN' && $item['cEAN'] !== '') ? $item['cEAN'] : null;
                    $produto->ncm = $item['NCM'];
                    $produto->cest = $item['CEST'] ?? null;
                    $produto->unidade_comercial = $item['uCom'] ?? 'UN';
                    $produto->unidade_tributavel = $item['uCom'] ?? 'UN'; // Assume a mesma por padrão
                    $produto->cfop_externo = $item['CFOP'] ?? null;
                    $produto->categoria_id = $categorizer->sugerirCategoria($produto->nome); // Categorização Inteligente
                    $produto->estoque = 0; // Será somado abaixo
                    $produto->origem = 0; // Nacional por padrão
                    $produto->usuario_id = auth()->id(); // Define o usuário criador
                    $produto->ativo = true;
                }

                if ($produto) {
                    $produto->preco_custo = $precoCusto;
                    $produto->preco_venda = $precoVenda;

                    if ($atualizarEstoque) {
                        $produto->estoque += $quantidade;
                    }

                    $produto->save();

                    // Associar Fornecedor
                    if (!$produto->fornecedores()->where('fornecedor_id', $fornecedor->id)->exists()) {
                        $produto->fornecedores()->attach($fornecedor->id, [
                            'preco_custo' => $precoCusto,
                            'codigo_produto_fornecedor' => $item['cProd']
                        ]);
                    } else {
                        // Update pivot price
                        $produto->fornecedores()->updateExistingPivot($fornecedor->id, [
                            'preco_custo' => $precoCusto,
                            'codigo_produto_fornecedor' => $item['cProd']
                        ]);
                    }

                    // Opcional: Registrar movimentação de estoque
                }
            }

            $nota->update(['status' => 'processada']);

            DB::commit();

            return redirect()->route('notas-entrada.index')->with('success', 'Nota processada com sucesso! Produtos e estoque atualizados.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar nota: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao processar nota: ' . $e->getMessage());
        }
    }

    public function espelho($id)
    {
        $nota = NotaEntrada::findOrFail($id);

        if (!$nota->xml_content) {
            return redirect()->route('notas-entrada.index')->with('error', 'Nota sem XML para visualizar.');
        }

        $xml = simplexml_load_string($nota->xml_content);

        // Localiza infNFe
        $infNFe = isset($xml->NFe->infNFe) ? $xml->NFe->infNFe : ($xml->infNFe ?? null);
        if (!$infNFe) {
            $ns = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('n', $ns[''] ?? 'http://www.portalfiscal.inf.br/nfe');
            $infNFe = $xml->xpath('//n:infNFe')[0] ?? null;
        }
        if (!$infNFe) {
            return redirect()->route('notas-entrada.index')->with('error', 'Estrutura do XML inválida para visualização.');
        }

        // Cabeçalho
        $cabecalho = [
            'emitente_nome' => (string) ($infNFe->emit->xNome ?? ''),
            'emitente_cnpj' => (string) ($infNFe->emit->CNPJ ?? ''),
            'numero_nfe' => (string) ($infNFe->ide->nNF ?? ''),
            'serie' => (string) ($infNFe->ide->serie ?? ''),
            'valor_total' => (float) ($infNFe->total->ICMSTot->vNF ?? 0),
            'data_emissao' => (string) ($infNFe->ide->dhEmi ?? '')
        ];

        // Itens de/para
        $itens = [];
        foreach ($infNFe->det as $det) {
            $prod = $det->prod;
            $produtoInterno = null;
            if (!empty((string)$prod->cEAN) && (string)$prod->cEAN !== 'SEM GTIN') {
                $produtoInterno = \App\Models\Produto::where('codigo_barras', (string)$prod->cEAN)->first();
            }
            $itens[] = [
                'xml_nome' => (string) $prod->xProd,
                'xml_ean' => (string) $prod->cEAN,
                'xml_unidade' => (string) $prod->uCom,
                'xml_qtd' => (float) $prod->qCom,
                'xml_custo' => (float) $prod->vUnCom,
                'produto_interno' => $produtoInterno
            ];
        }

        // Duplicatas
        $duplicatas = [];
        if (isset($infNFe->cobr) && isset($infNFe->cobr->dup)) {
            foreach ($infNFe->cobr->dup as $dup) {
                $duplicatas[] = [
                    'nDup' => (string) ($dup->nDup ?? ''),
                    'vDup' => (float) ($dup->vDup ?? 0),
                    'dVenc' => (string) ($dup->dVenc ?? '')
                ];
            }
        }

        // Configuração de markup padrão
        $metodoLucro = \App\Models\Configuracao::get('produtos_metodo_lucro', 'markup');
        $markupPadrao = (float) \App\Models\Configuracao::get('produtos_markup_padrao', '30');

        return view('content.fiscal.espelho', compact('nota', 'cabecalho', 'itens', 'duplicatas', 'metodoLucro', 'markupPadrao'));
    }

    public function espelhoXmlPdf($id)
    {
        $nota = NotaEntrada::findOrFail($id);
        if (empty($nota->xml_content)) {
            return redirect()->route('notas-entrada.index')->with('error', 'XML não disponível para esta nota.');
        }
        try {
            $xml = $nota->xml_content;
            $danfe = new CustomDanfe($xml);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('Powered by JBTech Informática', false);
            $danfe->exibirTextoFatura = true;

            $logoPath = public_path('assets/img/front-pages/landing-page/jblogo_black.png');
            if (!file_exists($logoPath)) {
                $fallback = public_path('assets/img/branding/logo.png');
                $logoPath = file_exists($fallback) ? $fallback : '';
            }
            $logo = $logoPath ?: '';
            $pdf = $danfe->render($logo);
            return Response::make($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="NFe_' . ($nota->numero_nfe ?? $nota->chave_acesso) . '.pdf"'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Erro ao gerar DANFE para NotaEntrada: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar DANFE: ' . $e->getMessage());
        }
    }
}
