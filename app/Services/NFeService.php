<?php

namespace App\Services;

use App\Models\NotaFiscal;
use App\Models\NotaFiscalServico;
use App\Models\Venda;
use App\Models\Clientes;
use App\Models\Produto;
use App\Models\Configuracao;
use App\Models\NaturezaOperacao;
use App\Models\NotaEntrada;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NFeEnviada;
use Barryvdh\DomPDF\Facade\Pdf;
use NFePHP\NFe\Make;
use App\Services\CustomDanfe;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use stdClass;
use Exception;
use App\Services\NFSeService;

use App\Services\InventoryService;

class NFeService
{
    protected $config;
    protected $certificate;
    protected $tools;
    protected $inventoryService;

    public function __construct()
    {
        $this->inventoryService = new InventoryService();

        // SEMPRE usa o banco de dados (múltiplas empresas podem usar o sistema)
        $this->config = [
            'atualizacao' => date('Y-m-d H:i:s'),
            'tpAmb' => (int) Configuracao::get('nfe_ambiente') ?: 2,
            'razaosocial' => Configuracao::get('nfe_razao_social') ?: Configuracao::get('empresa_nome') ?: 'RAZÃO SOCIAL NÃO CONFIGURADA',
            'siglaUF' => Configuracao::get('empresa_uf') ?: 'RJ',
            'cnpj' => Configuracao::get('nfe_cnpj') ?: Configuracao::get('empresa_cnpj') ?: '',
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'CSC' => Configuracao::get('nfe_csc') ?: '',
            'CSCid' => Configuracao::get('nfe_csc_id') ?: '',
        ];

        // Tenta carregar o certificado (pode falhar se não configurado, mas não deve parar o construtor)
        try {
            $this->loadCertificate();
        } catch (\Exception $e) {
            // Log::warning('NFeService: Certificado não carregado no construtor: ' . $e->getMessage());
        }
    }

    protected function loadCertificate()
    {
        try {
            $certPassword = Configuracao::get('nfe_cert_password');
            if (empty($certPassword)) {
                // Tenta pegar senha debug se existir
                $certPassword = Configuracao::get('nfe_cert_password', null, null);
                if (empty($certPassword)) {
                    throw new Exception('Senha do certificado não configurada.');
                }
            }

            $certData = Configuracao::get('nfe_cert_data');
            $certContent = null;

            if ($certData) {
                // Prioridade: Banco de Dados (base64)
                $certContent = base64_decode($certData);

                // Restaura o arquivo em disco se não existir (para compatibilidade/Heroku)
                // O Heroku limpa o disco a cada restart, então sempre verificamos
                $certPathConfig = Configuracao::get('nfe_cert_path');
                $certPath = storage_path('app/certificates/' . ($certPathConfig ?: 'certificado.pfx'));

                if (!file_exists($certPath)) {
                    // Garanta que o diretório existe
                    $dir = dirname($certPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    file_put_contents($certPath, $certContent);
                }
            } else {
                // Fallback: Arquivo local (storage)
                $certPathConfig = Configuracao::get('nfe_cert_path');
                $certPath = storage_path('app/certificates/' . ($certPathConfig ?: 'certificado.pfx'));
                if (file_exists($certPath)) {
                    $certContent = file_get_contents($certPath);
                }
            }

            if (!$certContent) {
                throw new Exception('Certificado digital não encontrado (Banco ou Arquivo).');
            }

            // Validação de Integridade do Certificado
            if (strlen($certContent) < 100) {
                throw new Exception('Conteúdo do certificado inválido ou corrompido (tamanho insuficiente).');
            }

            // Salva em arquivo temporário seguro se necessário (Heroku /tmp/)
            // A biblioteca NFePHP aceita o conteúdo binário diretamente em Certificate::readPfx
            // Mas para garantir compatibilidade com ambientes restritos, seguimos o padrão simples.

            $this->certificate = Certificate::readPfx($certContent, $certPassword);
            $this->tools = new Tools(json_encode($this->config), $this->certificate);
            $this->tools->model('55');
        } catch (\Exception $e) {
            Log::error('Erro ao carregar certificado: ' . $e->getMessage());
            throw new Exception('Erro ao carregar certificado: ' . $e->getMessage());
        }
    }

    /**
     * Consulta Notas Destinadas (DistDFe) e realiza Manifestação de Ciência
     */
    public function consultarNotasDestinadas($ultimoNSU = null)
    {
        try {
            if (!$this->tools) {
                $this->loadCertificate();
            }

            if ($ultimoNSU === null) {
                $ultimoNSU = NotaEntrada::max('nsu') ?? '0';
            }

            // Busca novas notas na SEFAZ
            $resp = $this->tools->sefazDistDFe($ultimoNSU);

            $st = new Standardize();
            $std = $st->toStd($resp);

            if (is_array($std)) {
                $std = (object) $std;
            }

            if ($std->cStat == '137') { // Nenhum documento localizado
                return (object) [
                    'message' => 'Nenhum documento novo localizado',
                    'count' => 0,
                    'ultNSU' => $std->ultNSU ?? 0,
                    'maxNSU' => $std->maxNSU ?? $ultimoNSU
                ];
            }

            if ($std->cStat == '656') { // Consumo indevido
                Log::warning("NFeService: Consumo indevido (cStat 656). Aguarde 1 hora.");
                return (object) [
                    'cStat' => '656',
                    'message' => 'Consumo indevido detectado. O sistema aguardará automaticamente.',
                    'count' => 0,
                    'ultNSU' => $std->ultNSU ?? 0,
                    'maxNSU' => $std->maxNSU ?? $ultimoNSU
                ];
            }

            if (!in_array($std->cStat, ['138'])) {
                Log::error("NFeService: Erro SEFAZ (cStat {$std->cStat}): " . $resp);
                return (object) ['message' => "Erro retornado pela SEFAZ: {$std->cStat}", 'ultNSU' => 0, 'maxNSU' => 0];
            }

            // Retorna o objeto padrão da SEFAZ para que o Command possa processar manualmente
            // e ter controle total sobre o fluxo (logs, progresso, etc)
            return $std;
        } catch (\Exception $e) {
            Log::error("NFeService: Erro crítico em consultarNotasDestinadas: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Processa a resposta do DistDFe com normalização de array/objeto
     */
    public function parseDistDFeResponse($std)
    {
        $docs = [];
        if (isset($std->loteDistDFeInt->docZip)) {
            $docs = $std->loteDistDFeInt->docZip;
            // Normalização de array (correção do erro 'offset on string' solicitado pelo usuário)
            if (!is_array($docs)) {
                $docs = [$docs];
            }
        }

        $count = 0;
        $ultNSU = $std->ultNSU ?? 0;
        $maxNSU = $std->maxNSU ?? 0;

        foreach ($docs as $doc) {
            // Validação de tipo para evitar erros de iteração
            if (!is_object($doc)) {
                continue;
            }

            // Extrai atributos (NSU e Schema)
            $nsu = $doc->attributes()->NSU ?? $doc->NSU ?? null;
            $schema = $doc->attributes()->schema ?? $doc->schema ?? null;
            $content = (string) $doc; // O conteúdo compactado é o valor do nó

            if (!$nsu || !$schema) {
                continue;
            }

            try {
                $xml = gzdecode(base64_decode($content));

                if (strpos($schema, 'resNFe') !== false) {
                    $this->processarResumo($xml, $nsu);
                } elseif (strpos($schema, 'procNFe') !== false) {
                    $this->processarCompleta($xml, $nsu);
                }

                $count++;
            } catch (\Exception $e) {
                Log::error("NFeService: Erro ao processar NSU {$nsu}: " . $e->getMessage());
            }
        }

        return ['message' => "Sincronização concluída. {$count} documentos processados.", 'count' => $count, 'ultNSU' => $ultNSU, 'maxNSU' => $maxNSU];
    }

    protected function processarResumo($xml, $nsu)
    {
        $st = new Standardize();
        $std = $st->toStd($xml);

        $chave = $std->chNFe;

        // Verifica se NSU já existe em outra nota para evitar duplicação
        if ($nsu) {
            $nsuExistente = NotaEntrada::where('nsu', $nsu)->where('chave_acesso', '!=', $chave)->first();
            if ($nsuExistente) {
                Log::warning("NSU {$nsu} duplicado ignorado para chave {$chave}. Pertence à chave {$nsuExistente->chave_acesso}");
                return;
            }
        }

        $nota = NotaEntrada::firstOrNew(['chave_acesso' => $chave]);
        $nota->nsu = $nsu;
        $nota->emitente_cnpj = $std->CNPJ;
        $nota->emitente_nome = $std->xNome;
        $nota->valor_total = $std->vNF;
        $nota->data_emissao = $std->dhEmi;

        // Limpeza de Segurança: Garante status inicial (detectada) e não salva XML incompleto
        if ($nota->status != 'concluido') {
            $nota->status = 'detectada';
        }
        $nota->save();

        // Manifesta Ciência da Operação (210210) para liberar download do XML completo
        if ($nota->manifestacao != 'ciencia' && $nota->manifestacao != 'confirmada') {
            try {
                // Pequeno delay para evitar flood se houver muitas
                sleep(1);
                $resp = $this->tools->sefazManifesta($chave, '210210');

                $stdManif = $st->toStd($resp);
                if (isset($stdManif->cStat) && in_array($stdManif->cStat, ['128', '135', '573'])) {
                    $nota->manifestacao = 'ciencia';
                    $nota->save();
                }
            } catch (\Exception $e) {
                Log::warning("Erro ao manifestar ciência para {$chave}: " . $e->getMessage());
            }
        }
    }

    protected function processarCompleta($xml, $nsu)
    {
        $st = new Standardize();
        $std = $st->toStd($xml);

        // procNFe tem NFe e protNFe
        // Se for procNFe, a chave fica dentro de protNFe -> infProt -> chNFe
        $chave = null;
        if (isset($std->protNFe->infProt->chNFe)) {
            $chave = $std->protNFe->infProt->chNFe;
        } elseif (isset($std->NFe->infNFe->attributes->Id)) {
            $chave = substr($std->NFe->infNFe->attributes->Id, 3);
        }

        if (!$chave) {
            Log::warning("Não foi possível identificar a chave no XML completo NSU {$nsu}");
            return;
        }

        // Verifica se NSU já existe em outra nota para evitar duplicação
        if ($nsu) {
            $nsuExistente = NotaEntrada::where('nsu', $nsu)->where('chave_acesso', '!=', $chave)->first();
            if ($nsuExistente) {
                Log::warning("NSU {$nsu} duplicado ignorado para chave {$chave} (XML Completo). Pertence à chave {$nsuExistente->chave_acesso}");
                return;
            }
        }

        $nota = NotaEntrada::firstOrNew(['chave_acesso' => $chave]);
        $nota->nsu = $nsu;
        $nota->xml_content = $xml; // Salva XML completo no banco conforme solicitado
        $nota->status = 'concluido'; // Marca como concluido (Verde na View)

        // Extrai dados complementares
        if (isset($std->NFe->infNFe->ide)) {
            $nota->numero_nfe = $std->NFe->infNFe->ide->nNF;
            $nota->serie = $std->NFe->infNFe->ide->serie;
        }
        if (isset($std->NFe->infNFe->emit)) {
            $nota->emitente_nome = $std->NFe->infNFe->emit->xNome;
            $nota->emitente_cnpj = $std->NFe->infNFe->emit->CNPJ;
        }
        if (isset($std->NFe->infNFe->total->ICMSTot->vNF)) {
            $nota->valor_total = $std->NFe->infNFe->total->ICMSTot->vNF;
        }

        $nota->save();

        // Integração com Estoque (InventoryService)
        try {
            $this->inventoryService->importarItensDaNota($nota);
        } catch (\Exception $e) {
            Log::error("Erro ao importar itens da nota {$chave} para o estoque: " . $e->getMessage());
        }
    }

    /**
     * Tenta baixar o XML de uma nota específica pela chave
     */
    public function baixarPorChave($chave)
    {
        try {
            if (!$this->tools) {
                $this->loadCertificate();
            }

            // Tenta fazer o download direto (se permitido para o CNPJ/Estado)
            // Nota: Em muitas UF o download por chave foi restrito. O ideal é DistDFe.
            // Mas mantemos a tentativa para casos onde funciona ou para manifestação.

            // Verifica status atual
            $nota = NotaEntrada::where('chave_acesso', $chave)->first();

            // Se não tiver manifestação, faz ciência
            if (!$nota || ($nota->manifestacao != 'ciencia' && $nota->manifestacao != 'confirmada')) {
                $this->tools->sefazManifesta($chave, '210210');
                if ($nota) {
                    $nota->manifestacao = 'ciencia';
                    $nota->save();
                }
                sleep(2); // Delay para processamento na SEFAZ
            }

            // Tenta download
            $resp = $this->tools->sefazDownload($chave);

            $st = new Standardize();
            $std = $st->toStd($resp);

            if ($std->cStat == '656') { // Consumo indevido
                return ['status' => 'error', 'cStat' => '656', 'message' => "Consumo indevido (656)."];
            }

            // 104 = Processado, 138 = Documento localizado para o destinatário
            if ($std->cStat != '104' && $std->cStat != '138') {
                return ['status' => 'error', 'cStat' => $std->cStat ?? '0', 'message' => "Erro SEFAZ: {$std->cStat} - " . ($std->xMotivo ?? '')];
            }

            // Se vier docZip, processa
            if (isset($std->loteDistDFeInt->docZip)) {
                // Reutiliza o parser para garantir processamento padrão (NSU, Schema, etc)
                $this->parseDistDFeResponse($std);

                $doc = $std->loteDistDFeInt->docZip;
                if (is_array($doc)) $doc = $doc[0];

                $content = (string) $doc;
                // O conteúdo vem em GZip + Base64
                $xml_puro = gzdecode(base64_decode($content));

                // LOG TEMPORÁRIO DE DEBUG (Solicitado pelo Usuário)
                if ($xml_puro) {
                    Log::info("Nota {$chave} baixada com " . strlen($xml_puro) . " caracteres.");
                }

                // --- RETRY LOGIC (Solicitação do Usuário) ---
                // Se for detectado apenas um resumo (resNFe), tenta baixar novamente
                // pois pode ser que a manifestação tenha acabado de ocorrer.
                if ($xml_puro && strpos($xml_puro, '<resNFe') !== false) {
                    Log::info("[Sistema] - Resumo detectado para {$chave}. Tentando baixar XML completo novamente...");
                    sleep(2);
                    try {
                        $respRetry = $this->tools->sefazDownload($chave);
                        $stdRetry = $st->toStd($respRetry);

                        if (isset($stdRetry->loteDistDFeInt->docZip)) {
                            $this->parseDistDFeResponse($stdRetry);
                            $docRetry = $stdRetry->loteDistDFeInt->docZip;
                            if (is_array($docRetry)) $docRetry = $docRetry[0];

                            $contentRetry = (string) $docRetry;
                            $xmlRetry = gzdecode(base64_decode($contentRetry));

                            // Se o novo XML for válido, substitui o anterior
                            if ($xmlRetry) {
                                $xml_puro = $xmlRetry;
                                $doc = $docRetry;
                                $content = $contentRetry;
                                $std = $stdRetry; // Atualiza o std principal para usar cStat correto se precisar
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("[Sistema] - Falha no retry de download para {$chave}: " . $e->getMessage());
                    }
                }
                // --- FIM RETRY LOGIC ---

                // Validação de string XML válida e segura
                // PRIORIDADE: Só aceita como sucesso se tiver nfeProc ou infNFe (XML Completo com Itens)
                // Se vier resNFe, mesmo que venha no docZip, é incompleto.
                // AJUSTE: Verificação de namespace (nfe:nfeProc) removendo a obrigatoriedade do '<' colado
                $temNFeProc = stripos($xml_puro, 'nfeProc') !== false;
                $temInfNFe = stripos($xml_puro, 'infNFe') !== false;
                $temResNFe = stripos($xml_puro, 'resNFe') !== false;

                if ($xml_puro && $temResNFe && !$temNFeProc) {
                    Log::warning("[Sistema] - Apenas resumo (resNFe) obtido para {$chave}. Mantendo status 'detectada'.");
                    // Não salvamos o XML de resumo para não poluir o banco com dados incompletos
                    // Retornamos erro para que o robô tente novamente no próximo ciclo
                    return ['status' => 'error', 'message' => 'SEFAZ retornou apenas resumo. Tentando novamente no próximo ciclo.'];
                }

                if ($xml_puro && ($temNFeProc || $temInfNFe)) {

                    // Se chegou aqui, é um XML Completo (nfeProc/infNFe)
                    NotaEntrada::where('chave_acesso', $chave)->update([
                        'xml_content' => $xml_puro,
                        'status' => 'concluido'
                    ]);

                    LogService::cagueta("[Sistema] - XML da Nota {$chave} recuperado com sucesso via chave direta.");
                } else {
                    Log::warning("[Sistema] - Falha na descompactação ou XML inválido para a chave {$chave}.");
                    return ['status' => 'error', 'message' => 'XML Inválido ou Corrompido.'];
                }

                // Tratamento robusto para extração de atributos (evita crash 'attributes() on string')
                $nsu = '0';
                $schema = 'procNFe';

                if (is_object($doc)) {
                    $nsu = $doc->attributes()->NSU ?? $doc->NSU ?? '0';
                    $schema = $doc->attributes()->schema ?? $doc->schema ?? 'procNFe';
                } else {
                    // Fallback para quando $doc virou string ou não é objeto
                    try {
                        // Usa a resposta original ($resp) ou a do retry se tiver sido atualizada?
                        // O $resp original ainda contém o XML da primeira tentativa.
                        // Se houve retry e sucesso, $doc foi atualizado, mas $resp não necessariamente (variável local).
                        // Vamos tentar extrair do $content que é garantido ser o docZip atual.
                        // Mas attributes() precisa do XML da estrutura do lote.
                        // Simplificação: Se já temos sucesso no download, NSU/Schema são secundários aqui.
                        // Vamos usar valores padrão se não der pra extrair.
                        $nsu = '0';
                    } catch (\Exception $e) {
                        Log::warning("Falha no fallback de extração de NSU: " . $e->getMessage());
                    }
                }

                return [
                    'status' => 'success',
                    'nsu' => $nsu,
                    'schema' => $schema,
                    'content' => (string)$doc
                ];
            }

            Log::info("[Sistema] - Tentativa de download manual para a chave {$chave}. Sucesso: Não (Sem docZip).");
            return ['status' => 'error', 'message' => 'Nenhum XML retornado no download.'];
        } catch (\Exception $e) {
            Log::error("Erro ao baixar nota {$chave}: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtém informações do certificado digital
     */
    public function getCertificadoInfo()
    {
        try {
            // Carrega o certificado se ainda não foi carregado
            if (!$this->certificate) {
                $this->loadCertificate();
            }

            if (!$this->certificate) {
                return null;
            }

            $validTo = $this->certificate->getValidTo(); // Retorna DateTime
            $validFrom = $this->certificate->getValidFrom(); // Retorna DateTime

            // Calcula dias restantes
            $hoje = new \DateTime();
            $diasRestantes = 0;
            if ($validTo > $hoje) {
                $diff = $hoje->diff($validTo);
                $diasRestantes = (int)$diff->days;
            }

            return [
                'valido_ate' => $validTo,
                'valido_de' => $validFrom,
                'expirado' => $this->certificate->isExpired(),
                'dias_restantes' => $diasRestantes,
                'cnpj' => $this->certificate->getCnpj(),
                'razao_social' => $this->certificate->getCompanyName()
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao obter info do certificado: ' . $e->getMessage());
            return ['erro' => $e->getMessage()];
        }
    }

    /**
     * Consulta o status de uma NF-e
     */
    public function consultarStatus(NotaFiscal $notaFiscal)
    {
        try {
            if (!$this->tools) {
                $this->loadCertificate();
            }

            if (empty($notaFiscal->chave_acesso)) {
                throw new Exception('Esta nota fiscal não possui chave de acesso para consulta.');
            }

            $chave = $notaFiscal->chave_acesso;
            $response = $this->tools->sefazConsultaChave($chave);

            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();

            if (isset($std->protNFe->infProt->cStat)) {
                $cStat = $std->protNFe->infProt->cStat;
                $xMotivo = $std->protNFe->infProt->xMotivo;

                if ($cStat == '100') {
                    $notaFiscal->status = 'autorizada';
                    $notaFiscal->motivo_rejeicao = null;

                    if (isset($std->protNFe) && !empty($notaFiscal->xml)) {
                        $xmlAtual = $notaFiscal->xml;
                        if (stripos($xmlAtual, '<nfeProc') === false && stripos($xmlAtual, '<NFeProc') === false) {
                            try {
                                $xmlProtocolado = Complements::toAuthorize($xmlAtual, $response);
                                $notaFiscal->xml = $xmlProtocolado;
                            } catch (\Exception $e) {
                                Log::warning("Erro ao anexar protocolo ao XML na consulta: " . $e->getMessage());
                            }
                        }
                    }

                    if (isset($std->protNFe->infProt->nProt)) {
                        $notaFiscal->protocolo = $std->protNFe->infProt->nProt;
                    }
                } elseif (in_array($cStat, ['101', '151', '155'])) { // Cancelada
                    $notaFiscal->status = 'cancelada';
                } elseif (in_array($cStat, ['110', '301', '302'])) { // Denegada
                    $notaFiscal->status = 'denegada';
                }

                $notaFiscal->save();
                Log::info("Consulta NF-e {$chave}: {$cStat} - {$xMotivo}");

                return [
                    'cStat' => $cStat,
                    'xMotivo' => $xMotivo,
                    'protocolo' => $std->protNFe->infProt ?? null
                ];
            }

            return $std;
        } catch (\Exception $e) {
            Log::error('Erro ao consultar status da NF-e: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cria uma NF-e pendente a partir de uma venda (Rascunho)
     */
    public function criarNotaDeVenda(Venda $venda)
    {
        try {
            $notaExistente = NotaFiscal::where('venda_id', $venda->id)
                ->where('status', 'autorizada')
                ->first();

            if ($notaExistente) {
                throw new Exception('Já existe uma NF-e autorizada para esta venda.');
            }

            $venda->load(['cliente.endereco', 'produtos', 'user']);
            $cliente = $venda->cliente;

            if (!$cliente->endereco) {
                throw new Exception('Cliente não possui endereço cadastrado.');
            }

            $emitente = $this->getDadosEmitente();
            $destinatario = $this->getDadosDestinatario($cliente);

            $produtos = [];
            $itemIndex = 1;
            $valorTotalNFe = 0;

            $servicosNFSe = [];
            $valorTotalServicos = 0;

            foreach ($venda->produtos as $produto) {
                if ($produto->tipo_item === '09' || !empty($produto->servico_id)) {
                    $servicosNFSe[] = $produto;
                    $valorTotalServicos += ($produto->pivot->quantidade * $produto->pivot->valor_unitario);
                    continue;
                }

                $dadosProd = $this->getDadosProduto($produto, $venda, $itemIndex++);
                $produtos[] = $dadosProd;

                $vProd = isset($dadosProd['vProd']) ? (float)$dadosProd['vProd'] : (isset($dadosProd['valor_total']) ? (float)$dadosProd['valor_total'] : 0);
                $valorTotalNFe += $vProd;
            }

            if (!empty($servicosNFSe)) {
                try {
                    $this->gerarNFSeParaVenda($venda, $servicosNFSe, $valorTotalServicos);
                } catch (\Exception $e) {
                    Log::error('Erro ao gerar NFS-e automática para venda #' . $venda->id . ': ' . $e->getMessage());
                }
            }

            if (empty($produtos)) {
                return null;
            }

            $naturezaPadrao = NaturezaOperacao::where('tipo', 'saida')
                ->where('padrao', true)
                ->first();

            $naturezaDescricao = $naturezaPadrao?->descricao ?? 'VENDA DE MERCADORIA';
            $finalidade = $naturezaPadrao?->finNFe ?? 1;

            $vendedor = $venda->user->name ?? 'N/A';
            $observacoes = "Venda: #{$venda->id} - Vendedor: {$vendedor}";

            if (!empty($venda->observacoes)) {
                $observacoes .= " | Obs: " . $venda->observacoes;
            }

            $notaFiscal = NotaFiscal::create([
                'venda_id' => $venda->id,
                'cliente_id' => $venda->cliente_id,
                'numero_nfe' => null,
                'chave_acesso' => null,
                'status' => 'pendente',
                'valor_total' => $valorTotalNFe,
                'data_emissao' => null,
                'dados_destinatario' => $destinatario,
                'produtos' => $produtos,
                'natureza_operacao' => $naturezaDescricao,
                'tipo_documento' => 1,
                'finalidade' => $finalidade,
                'observacoes' => $observacoes,
            ]);

            return $notaFiscal;
        } catch (Exception $e) {
            Log::error('Erro ao criar rascunho de NF-e: ' . $e->getMessage());
            throw $e;
        }
    }

    public function criarNotaAvulsa($dados)
    {
        try {
            $emitente = $this->getDadosEmitente();
            $destinatario = null;
            $clienteId = null;

            if ($dados['destinatario_tipo'] == 'proprio') {
                $destinatario = (object) [
                    'xNome' => $emitente->xNome,
                    'cpf_cnpj' => $emitente->CNPJ,
                    'IE' => $emitente->IE,
                    'email' => $emitente->email ?? '',
                    'xLgr' => $emitente->xLgr,
                    'nro' => $emitente->nro,
                    'xCpl' => $emitente->xCpl ?? '',
                    'xBairro' => $emitente->xBairro,
                    'cMun' => $emitente->cMun,
                    'xMun' => $emitente->xMun,
                    'UF' => $emitente->UF,
                    'CEP' => $emitente->CEP,
                ];
            } elseif ($dados['destinatario_tipo'] == 'cliente') {
                $cliente = Clientes::with('endereco')->findOrFail($dados['destinatario_id']);

                $temEndManual = !empty($dados['destinatario_endereco_logradouro']);

                if (!$cliente->endereco && !$temEndManual) {
                    throw new Exception('Cliente sem endereço cadastrado e nenhum endereço informado manualmente.');
                }

                if ($cliente->endereco) {
                    $destinatario = (object) $this->getDadosDestinatario($cliente);
                } else {
                    $destinatario = (object) [
                        'xNome' => $cliente->nome,
                        'cpf_cnpj' => $cliente->cpf_cnpj,
                        'IE' => $cliente->inscricao_estadual ?? '',
                        'email' => $cliente->email,
                        'cMun' => '9999999',
                        'xLgr' => '',
                        'nro' => '',
                        'xCpl' => '',
                        'xBairro' => '',
                        'xMun' => '',
                        'UF' => '',
                        'CEP' => ''
                    ];
                }

                if ($temEndManual) {
                    $destinatario->xLgr = $dados['destinatario_endereco_logradouro'];
                    $destinatario->nro = $dados['destinatario_endereco_numero'];
                    $destinatario->xCpl = $dados['destinatario_endereco_complemento'] ?? '';
                    $destinatario->xBairro = $dados['destinatario_endereco_bairro'];
                    $destinatario->xMun = $dados['destinatario_endereco_cidade'];
                    $destinatario->UF = $dados['destinatario_endereco_uf'];
                    $destinatario->CEP = preg_replace('/[^0-9]/', '', $dados['destinatario_endereco_cep']);
                }

                $clienteId = $cliente->id;
            } elseif ($dados['destinatario_tipo'] == 'fornecedor') {
                $fornecedor = \App\Models\Fornecedor::findOrFail($dados['destinatario_id']);

                $temEndManual = !empty($dados['destinatario_endereco_logradouro']);

                $destinatario = (object) [
                    'xNome' => $fornecedor->nome,
                    'cpf_cnpj' => $fornecedor->cnpj,
                    'IE' => '',
                    'email' => $fornecedor->email,
                    'xLgr' => $temEndManual ? $dados['destinatario_endereco_logradouro'] : $fornecedor->endereco,
                    'nro' => $temEndManual ? $dados['destinatario_endereco_numero'] : $fornecedor->numero,
                    'xCpl' => $temEndManual ? ($dados['destinatario_endereco_complemento'] ?? '') : '',
                    'xBairro' => $temEndManual ? $dados['destinatario_endereco_bairro'] : $fornecedor->bairro,
                    'cMun' => '9999999',
                    'xMun' => $temEndManual ? $dados['destinatario_endereco_cidade'] : $fornecedor->cidade,
                    'UF' => $temEndManual ? $dados['destinatario_endereco_uf'] : $fornecedor->uf,
                    'CEP' => $temEndManual ? preg_replace('/[^0-9]/', '', $dados['destinatario_endereco_cep']) : $fornecedor->cep,
                ];
            }

            $produtos = [];
            $valorTotal = 0;
            foreach ($dados['produtos'] as $index => $item) {
                $prodDb = Produto::find($item['id']);
                $totalItem = $item['quantidade'] * $item['valor_unitario'];
                $valorTotal += $totalItem;

                $produtos[] = [
                    'item' => $index + 1,
                    'cProd' => $prodDb->id,
                    'cEAN' => $prodDb->codigo_barras ?? 'SEM GTIN',
                    'xProd' => $prodDb->nome,
                    'NCM' => $prodDb->ncm,
                    'CFOP' => $item['cfop'],
                    'uCom' => $prodDb->unidade,
                    'qCom' => $item['quantidade'],
                    'vUnCom' => $item['valor_unitario'],
                    'vProd' => $totalItem,
                    'cEANTrib' => 'SEM GTIN',
                    'uTrib' => $prodDb->unidade,
                    'qTrib' => $item['quantidade'],
                    'vUnTrib' => $item['valor_unitario'],
                    'indTot' => 1,
                    'orig' => $prodDb->origem ?? 0,
                    'CSOSN' => $prodDb->csosn_icms ?? '102',
                    'vBC' => 0,
                    'pICMS' => 0,
                    'vICMS' => 0,
                    'pIPI' => 0,
                    'vIPI' => 0,
                    'pPIS' => 0,
                    'vPIS' => 0,
                    'pCOFINS' => 0,
                    'vCOFINS' => 0,
                ];
            }

            $natureza = !empty($dados['natureza_operacao'])
                ? NaturezaOperacao::where('descricao', $dados['natureza_operacao'])->first()
                : null;

            $finalidade = $natureza?->finNFe ?? 1;

            $vendedor = auth()->user()->name ?? 'N/A';
            $observacoes = 'Emissão Avulsa: ' . $dados['natureza_operacao'] . " - Vendedor: {$vendedor}";

            $notaFiscal = NotaFiscal::create([
                'venda_id' => null,
                'cliente_id' => $clienteId,
                'numero_nfe' => null,
                'chave_acesso' => null,
                'status' => 'pendente',
                'valor_total' => $valorTotal,
                'data_emissao' => null,
                'dados_destinatario' => $destinatario,
                'produtos' => $produtos,
                'natureza_operacao' => $dados['natureza_operacao'],
                'tipo_documento' => $dados['tipo_documento'],
                'finalidade' => $finalidade,
                'observacoes' => $observacoes,
                'dados_pagamento' => $dados['pagamento'] ?? null,
            ]);

            return $notaFiscal;
        } catch (Exception $e) {
            Log::error('Erro ao criar nota avulsa: ' . $e->getMessage());
            throw $e;
        }
    }

    public function gerarPdf(NotaFiscal $notaFiscal)
    {
        try {
            $xml = $notaFiscal->xml;

            if (empty($xml)) {
                $xml = $this->montarXml($notaFiscal);
            }

            $danfe = new CustomDanfe($xml);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('Powered by JBTech Informática', false);
            $danfe->exibirTextoFatura = true;

            $logoPath = public_path('assets/img/front-pages/landing-page/jblogo_black.png');
            if (!file_exists($logoPath)) {
                $fallback = public_path('assets/img/branding/logo.png');
                $logoPath = file_exists($fallback) ? $fallback : '';
            }

            $logo = '';
            if ($logoPath !== '') {
                $info = getimagesize($logoPath);
                if ($info && isset($info[2]) && $info[2] === IMAGETYPE_PNG) {
                    $image = imagecreatefrompng($logoPath);
                    if ($image) {
                        $width = imagesx($image);
                        $height = imagesy($image);

                        $maxWidth = 160;
                        $maxHeight = 85;

                        $newWidth = $width;
                        $newHeight = $height;

                        if ($width > $maxWidth || $height > $maxHeight) {
                            $ratio = min($maxWidth / $width, $maxHeight / $height);
                            $newWidth = (int)($width * $ratio);
                            $newHeight = (int)($height * $ratio);
                        }

                        $marginTop = 20;
                        $finalHeight = $newHeight + $marginTop;

                        $output = imagecreatetruecolor($newWidth, $finalHeight);
                        $white = imagecolorallocate($output, 255, 255, 255);
                        imagefilledrectangle($output, 0, 0, $newWidth, $finalHeight, $white);

                        imagecopyresampled($output, $image, 0, $marginTop, 0, 0, $newWidth, $newHeight, $width, $height);

                        ob_start();
                        imagejpeg($output, null, 100);
                        $jpegData = ob_get_clean();

                        imagedestroy($image);
                        imagedestroy($output);

                        $tmpFile = tempnam(sys_get_temp_dir(), 'nfelogo_') . '.jpg';
                        file_put_contents($tmpFile, $jpegData);
                        $logo = $tmpFile;
                    }
                } else {
                    $logo = $logoPath;
                }
            }

            $pdf = $danfe->render($logo);
            return $pdf;
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            throw new Exception("Erro ao gerar visualização da Nota: " . $e->getMessage());
        }
    }

    public function montarXml(NotaFiscal $notaFiscal)
    {
        // Cria a estrutura da NF-e
        $make = new Make();

        // Inicializa a tag infNFe
        $stdInf = new \stdClass();
        $stdInf->versao = '4.00';
        $make->taginfNFe($stdInf);

        // 1. Identificação (tagide)
        $serie = $notaFiscal->serie ?: (Configuracao::get('nfe_serie') ?: 1);
        $nfeNumero = $notaFiscal->numero_nfe ?: $this->getNextNfeNumber($serie);

        if (!$notaFiscal->numero_nfe) {
            $notaFiscal->numero_nfe = $nfeNumero;
            $notaFiscal->serie = $serie;
        }

        $emitente = $this->getDadosEmitente();

        if (empty($emitente->xLgr)) {
            $emitente->xLgr = 'Rua Não Cadastrada';
            $emitente->nro = '000';
            $emitente->xBairro = 'Bairro';
            $emitente->cMun = '3304557';
            $emitente->xMun = 'Rio de Janeiro';
            $emitente->UF = 'RJ';
            $emitente->CEP = '20000000';
        }

        $stdEmit = new \stdClass();
        $stdEmit->xNome = $emitente->xNome;
        $stdEmit->xFant = $emitente->xFant;

        $ieEmit = isset($emitente->IE) ? trim((string) $emitente->IE) : '';
        if ($ieEmit !== '') {
            if (strcasecmp($ieEmit, 'ISENTO') === 0) {
                $stdEmit->IE = 'ISENTO';
            } else {
                $ieDigits = preg_replace('/\D/', '', $ieEmit);
                if ($ieDigits !== '') {
                    $stdEmit->IE = $ieDigits;
                }
            }
        }

        $stdEmit->CRT = $emitente->CRT;
        $stdEmit->CNPJ = $emitente->CNPJ;

        $stdEmit->xLgr = $emitente->xLgr;
        $stdEmit->nro = $emitente->nro;
        $stdEmit->xCpl = $emitente->xCpl ?? '';
        $stdEmit->xBairro = $emitente->xBairro;
        $stdEmit->cMun = $emitente->cMun;
        $stdEmit->xMun = $emitente->xMun;
        $stdEmit->UF = $emitente->UF;
        $stdEmit->CEP = $emitente->CEP;
        $stdEmit->cPais = $emitente->cPais;
        $stdEmit->xPais = $emitente->xPais;
        $stdEmit->fone = $emitente->fone ?? '';

        $stdIde = new \stdClass();
        $stdIde->cUF = $this->getCodigoUF($this->config['siglaUF']);
        $stdIde->cNF = rand(10000000, 99999999);
        $stdIde->natOp = $notaFiscal->natureza_operacao ?? 'VENDA DE MERCADORIA';
        $stdIde->mod = 55;
        $stdIde->serie = $serie;
        $stdIde->nNF = $nfeNumero;
        $stdIde->dhEmi = $notaFiscal->data_emissao ? $notaFiscal->data_emissao->format('Y-m-d\TH:i:sP') : date('Y-m-d\TH:i:sP');

        if (!empty($notaFiscal->data_saida)) {
            $stdIde->dhSaiEnt = $notaFiscal->data_saida->format('Y-m-d\TH:i:sP');
        }

        $stdIde->tpNF = $notaFiscal->tipo_documento ?? 1;

        $destUF = $notaFiscal->dados_destinatario['UF'] ?? $this->config['siglaUF'];
        $stdIde->idDest = ($destUF == $emitente->UF) ? 1 : 2;
        if ($destUF == 'EX') $stdIde->idDest = 3;

        $stdIde->cMunFG = Configuracao::get('nfe_endereco_codigo_municipio') ?: '3304508';
        $stdIde->tpImp = 1;
        $stdIde->tpEmis = 1;
        $stdIde->cDV = 0;
        $stdIde->tpAmb = $this->config['tpAmb'];
        $stdIde->finNFe = $notaFiscal->finalidade ?? 1;
        $stdIde->indFinal = 1;
        $stdIde->indPres = 1;
        $stdIde->procEmi = 0;
        $stdIde->verProc = '4.0';

        if (!empty($notaFiscal->natureza_operacao)) {
            $naturezaModel = NaturezaOperacao::where('descricao', $notaFiscal->natureza_operacao)->first();
            if ($naturezaModel) {
                if (!is_null($naturezaModel->finNFe)) {
                    $stdIde->finNFe = (int) $naturezaModel->finNFe;
                }
                if (!is_null($naturezaModel->consumidor_final)) {
                    $stdIde->indFinal = $naturezaModel->consumidor_final ? 1 : 0;
                }
                if (!is_null($naturezaModel->indPres)) {
                    $stdIde->indPres = (int) $naturezaModel->indPres;
                }
            }
        }

        $make->tagide($stdIde);
        $make->tagemit($stdEmit);

        $stdEnderEmit = new \stdClass();
        $cepEmitente = preg_replace('/\D/', '', (string) $emitente->CEP);
        $stdEnderEmit->xLgr = $emitente->xLgr;
        $stdEnderEmit->nro = $emitente->nro;
        $stdEnderEmit->xCpl = $emitente->xCpl ?? '';
        $stdEnderEmit->xBairro = $emitente->xBairro;
        $stdEnderEmit->cMun = $emitente->cMun;
        $stdEnderEmit->xMun = $emitente->xMun;
        $stdEnderEmit->UF = $emitente->UF;
        $stdEnderEmit->CEP = $cepEmitente;
        $stdEnderEmit->cPais = $emitente->cPais;
        $stdEnderEmit->xPais = $emitente->xPais;
        $stdEnderEmit->fone = $emitente->fone ?? '';

        $make->tagenderEmit($stdEnderEmit);

        $destinatario = $notaFiscal->dados_destinatario;

        if ($notaFiscal->tipo_documento == 1 && $stdIde->idDest != 3) {
            $stdDest = new \stdClass();
            $stdDest->xNome = $destinatario['xNome'];

            if (!empty($destinatario['cpf_cnpj'])) {
                $doc = preg_replace('/\D/', '', $destinatario['cpf_cnpj']);
                if (strlen($doc) == 14) {
                    $stdDest->CNPJ = $doc;
                    $ieDest = isset($destinatario['IE']) ? trim((string)$destinatario['IE']) : '';
                    if ($ieDest !== '') {
                        if (strcasecmp($ieDest, 'ISENTO') === 0) {
                            $stdDest->IE = 'ISENTO';
                        } else {
                            $ieDigits = preg_replace('/\D/', '', $ieDest);
                            if ($ieDigits !== '') {
                                $stdDest->IE = $ieDigits;
                            } else {
                                $stdDest->indIEDest = 9;
                            }
                        }
                    } else {
                        $stdDest->indIEDest = 9;
                    }
                } else {
                    $stdDest->CPF = $doc;
                    $stdDest->indIEDest = 9;
                }
            }

            if (!empty($destinatario['email'])) {
                $stdDest->email = $destinatario['email'];
            }

            $make->tagdest($stdDest);

            $stdEnderDest = new \stdClass();
            $stdEnderDest->xLgr = $destinatario['xLgr'];
            $stdEnderDest->nro = $destinatario['nro'];
            $stdEnderDest->xCpl = $destinatario['xCpl'] ?? '';
            $stdEnderDest->xBairro = $destinatario['xBairro'];
            $stdEnderDest->cMun = $destinatario['cMun'];
            $stdEnderDest->xMun = $destinatario['xMun'];
            $stdEnderDest->UF = $destinatario['UF'];
            $stdEnderDest->CEP = preg_replace('/\D/', '', $destinatario['CEP']);
            $stdEnderDest->cPais = '1058';
            $stdEnderDest->xPais = 'BRASIL';

            $make->tagenderDest($stdEnderDest);
        }

        $itemIndex = 1;
        foreach ($notaFiscal->produtos as $produto) {
            $stdProd = new \stdClass();
            $stdProd->item = $itemIndex;
            $stdProd->cProd = $produto['cProd'];
            $stdProd->cEAN = !empty($produto['cEAN']) ? $produto['cEAN'] : 'SEM GTIN';
            $stdProd->xProd = $produto['xProd'];
            $stdProd->NCM = $produto['NCM'];
            $stdProd->CFOP = $produto['CFOP'];
            $stdProd->uCom = $produto['uCom'];
            $stdProd->qCom = $produto['qCom'];
            $stdProd->vUnCom = $produto['vUnCom'];
            $stdProd->vProd = $produto['vProd'];
            $stdProd->cEANTrib = !empty($produto['cEANTrib']) ? $produto['cEANTrib'] : 'SEM GTIN';
            $stdProd->uTrib = $produto['uTrib'];
            $stdProd->qTrib = $produto['qTrib'];
            $stdProd->vUnTrib = $produto['vUnTrib'];
            $stdProd->indTot = $produto['indTot'];

            $make->tagprod($stdProd);

            $stdImposto = new \stdClass();
            $stdImposto->item = $itemIndex;
            $make->tagimposto($stdImposto);

            if ($emitente->CRT == 1) { // Simples Nacional
                $stdICMS = new \stdClass();
                $stdICMS->item = $itemIndex;
                $stdICMS->CSOSN = $produto['CSOSN'];
                $stdICMS->orig = $produto['orig'];
                $stdICMS->modBC = 3;
                $stdICMS->vBC = 0.00;
                $stdICMS->pICMS = 0.00;
                $stdICMS->vICMS = 0.00;

                $make->tagICMSSN($stdICMS);
            } else { // Regime Normal
                $stdICMS = new \stdClass();
                $stdICMS->item = $itemIndex;
                $stdICMS->CST = '00';
                $stdICMS->orig = $produto['orig'];
                $stdICMS->modBC = 3;
                $stdICMS->vBC = 0.00;
                $stdICMS->pICMS = 0.00;
                $stdICMS->vICMS = 0.00;

                $make->tagICMS($stdICMS);
            }

            $stdPIS = new \stdClass();
            $stdPIS->item = $itemIndex;
            $stdPIS->CST = '07';
            $stdPIS->vBC = 0.00;
            $stdPIS->pPIS = 0.00;
            $stdPIS->vPIS = 0.00;
            $make->tagPIS($stdPIS);

            $stdCOFINS = new \stdClass();
            $stdCOFINS->item = $itemIndex;
            $stdCOFINS->CST = '07';
            $stdCOFINS->vBC = 0.00;
            $stdCOFINS->pCOFINS = 0.00;
            $stdCOFINS->vCOFINS = 0.00;
            $make->tagCOFINS($stdCOFINS);

            $itemIndex++;
        }

        $stdICMSTot = new \stdClass();
        $stdICMSTot->vBC = 0.00;
        $stdICMSTot->vICMS = 0.00;
        $stdICMSTot->vICMSDeson = 0.00;
        $stdICMSTot->vFCP = 0.00;
        $stdICMSTot->vBCST = 0.00;
        $stdICMSTot->vST = 0.00;
        $stdICMSTot->vFCPST = 0.00;
        $stdICMSTot->vFCPSTRet = 0.00;
        $stdICMSTot->vProd = $notaFiscal->valor_total;
        $stdICMSTot->vFrete = 0.00;
        $stdICMSTot->vSeg = 0.00;
        $stdICMSTot->vDesc = 0.00;
        $stdICMSTot->vII = 0.00;
        $stdICMSTot->vIPI = 0.00;
        $stdICMSTot->vIPIDevol = 0.00;
        $stdICMSTot->vPIS = 0.00;
        $stdICMSTot->vCOFINS = 0.00;
        $stdICMSTot->vOutro = 0.00;
        $stdICMSTot->vNF = $notaFiscal->valor_total;
        $stdICMSTot->vTotTrib = 0.00;

        $make->tagICMSTot($stdICMSTot);

        $stdTransp = new \stdClass();
        $stdTransp->modFrete = 9;
        $make->tagtransp($stdTransp);

        if ($notaFiscal->dados_pagamento) {
            // Implementar lógica de pagamento se necessário
        } else {
            $stdPag = new \stdClass();
            $stdPag->vTroco = 0;
            $make->tagpag($stdPag);

            $stdDetPag = new \stdClass();
            $stdDetPag->tPag = '90';
            $stdDetPag->vPag = $notaFiscal->valor_total;
            $stdDetPag->indPag = 0;
            $make->tagdetPag($stdDetPag);
        }

        try {
            $xml = $make->getXML();
            return $xml;
        } catch (\Exception $e) {
            throw new Exception("Erro ao gerar XML: " . $e->getMessage());
        }
    }

    private function getDadosEmitente()
    {
        return (object) [
            'xNome' => $this->config['razaosocial'],
            'xFant' => Configuracao::get('empresa_nome_fantasia') ?: $this->config['razaosocial'],
            'CNPJ' => preg_replace('/\D/', '', $this->config['cnpj']),
            'IE' => preg_replace('/\D/', '', Configuracao::get('nfe_ie') ?: ''),
            'CRT' => Configuracao::get('nfe_crt') ?: 1,
            'email' => Configuracao::get('empresa_email'),
            'xLgr' => Configuracao::get('empresa_endereco_logradouro'),
            'nro' => Configuracao::get('empresa_endereco_numero'),
            'xCpl' => Configuracao::get('empresa_endereco_complemento'),
            'xBairro' => Configuracao::get('empresa_endereco_bairro'),
            'cMun' => Configuracao::get('nfe_endereco_codigo_municipio'),
            'xMun' => Configuracao::get('empresa_endereco_cidade'),
            'UF' => Configuracao::get('empresa_uf'),
            'CEP' => Configuracao::get('empresa_endereco_cep'),
            'cPais' => '1058',
            'xPais' => 'BRASIL',
            'fone' => preg_replace('/\D/', '', Configuracao::get('empresa_telefone') ?: ''),
        ];
    }

    private function getDadosDestinatario($cliente)
    {
        $dest = [
            'xNome' => $cliente->nome,
            'cpf_cnpj' => $cliente->cpf_cnpj,
            'IE' => $cliente->inscricao_estadual,
            'email' => $cliente->email,
        ];

        if ($cliente->endereco) {
            $dest['xLgr'] = $cliente->endereco->logradouro;
            $dest['nro'] = $cliente->endereco->numero;
            $dest['xCpl'] = $cliente->endereco->complemento;
            $dest['xBairro'] = $cliente->endereco->bairro;
            $dest['cMun'] = '9999999'; // Deve ser obtido de uma tabela de municípios IBGE
            $dest['xMun'] = $cliente->endereco->cidade;
            $dest['UF'] = $cliente->endereco->uf;
            $dest['CEP'] = $cliente->endereco->cep;
        }

        return $dest;
    }

    private function getDadosProduto($produto, $venda, $itemIndex)
    {
        $totalItem = $produto->pivot->quantidade * $produto->pivot->valor_unitario;

        return [
            'item' => $itemIndex,
            'cProd' => $produto->id,
            'cEAN' => $produto->codigo_barras ?? 'SEM GTIN',
            'xProd' => $produto->nome,
            'NCM' => $produto->ncm,
            'CFOP' => '5102', // Deve ser dinâmico
            'uCom' => $produto->unidade,
            'qCom' => $produto->pivot->quantidade,
            'vUnCom' => $produto->pivot->valor_unitario,
            'vProd' => $totalItem,
            'cEANTrib' => 'SEM GTIN',
            'uTrib' => $produto->unidade,
            'qTrib' => $produto->pivot->quantidade,
            'vUnTrib' => $produto->pivot->valor_unitario,
            'indTot' => 1,
            'orig' => $produto->origem ?? 0,
            'CSOSN' => $produto->csosn_icms ?? '102',
            'vBC' => 0,
            'pICMS' => 0,
            'vICMS' => 0,
            'pIPI' => 0,
            'vIPI' => 0,
            'pPIS' => 0,
            'vPIS' => 0,
            'pCOFINS' => 0,
            'vCOFINS' => 0,
        ];
    }

    private function getCodigoUF($sigla)
    {
        $ufs = [
            'RO' => '11',
            'AC' => '12',
            'AM' => '13',
            'RR' => '14',
            'PA' => '15',
            'AP' => '16',
            'TO' => '17',
            'MA' => '21',
            'PI' => '22',
            'CE' => '23',
            'RN' => '24',
            'PB' => '25',
            'PE' => '26',
            'AL' => '27',
            'SE' => '28',
            'BA' => '29',
            'MG' => '31',
            'ES' => '32',
            'RJ' => '33',
            'SP' => '35',
            'PR' => '41',
            'SC' => '42',
            'RS' => '43',
            'MS' => '50',
            'MT' => '51',
            'GO' => '52',
            'DF' => '53'
        ];
        return $ufs[$sigla] ?? '33';
    }

    private function getNextNfeNumber($serie)
    {
        $lastNfe = NotaFiscal::where('serie', $serie)
            ->whereNotNull('numero_nfe')
            ->orderByRaw('CAST(numero_nfe AS UNSIGNED) DESC')
            ->first();

        return $lastNfe ? ($lastNfe->numero_nfe + 1) : 1;
    }

    protected function gerarNFSeParaVenda($venda, $servicos, $valorTotalServicos)
    {
        // Implementação simplificada para não quebrar o contrato
        // A lógica real estaria no NFSeService
    }
}
