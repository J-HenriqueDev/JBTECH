<?php

namespace App\Services;

use App\Models\NotaFiscalServico;
use App\Models\Configuracao;
use App\Models\Clientes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use NFePHP\Common\Certificate;
use Hadder\NfseNacional\Tools;
use Hadder\NfseNacional\Dps;
use stdClass;

class NFSeService
{
    protected $config;
    protected $certificate;
    protected $tools;

    public function __construct()
    {
        // Configuração básica
        $this->config = [
            'ambiente' => (int)Configuracao::get('nfse_ambiente', '2'), // 1=Produção, 2=Homologação
            'cnpj' => Configuracao::get('empresa_cnpj'),
            'im' => Configuracao::get('empresa_im'), // Inscrição Municipal
            'cod_municipio' => Configuracao::get('empresa_cod_municipio'), // Código IBGE do município
            'senha' => Configuracao::get('nfse_senha'), // Senha do certificado ou webservice
        ];

        // Carrega o certificado
        try {
            $this->loadCertificate();
        } catch (Exception $e) {
            Log::error('Erro ao carregar certificado na NFS-e: ' . $e->getMessage());
        }
    }

    /**
     * Gera e envia o DPS (Declaração de Prestação de Serviço) para a API Nacional
     *
     * @param NotaFiscalServico $nfse
     * @return array Resposta da API Nacional
     */
    public function emitir(NotaFiscalServico $nfse)
    {
        try {
            // 0. Atribui numeração RPS sequencial se não houver
            if (empty($nfse->numero_rps)) {
                $lastRps = (int) Configuracao::get('nfse_ultimo_numero', 0);
                $nfse->numero_rps = $lastRps + 1;
                // Série RPS: Se houver config específica usa, senão usa a geral ou '1'
                $nfse->serie_rps = Configuracao::get('nfse_serie') ?? Configuracao::get('nfe_serie', '1');
                $nfse->save();
            }

            // 1. Validação básica
            $this->validarDados($nfse);

            if (!$this->certificate) {
                $this->loadCertificate();
            }

            // 2. Instancia Tools
            // Config para Tools é um JSON string com tpamb
            $toolsConfig = json_encode(['tpamb' => $this->config['ambiente']]);
            $this->tools = new Tools($toolsConfig, $this->certificate);

            // 3. Gerar stdClass do DPS (Padrão Nacional)
            $std = $this->gerarDpsStd($nfse);

            // Instancia classe Dps para validar/renderizar (opcional, mas bom para garantir estrutura)
            $dps = new Dps($std);
            $xmlContent = $dps->render($std); // Isso gera o XML assinado internamente se precisar, mas o enviaDps faz isso

            // A lib pede o objeto stdClass para o Dps, mas o enviaDps recebe o XML?
            // Olhando Tools.php: enviaDps($content)
            // Olhando Dps.php: render() popula $this->dom e retorna nada?
            // Ah, Dps->render() popula o DOM.
            // Mas Tools->enviaDps($content) assina o $content.
            // Então eu preciso gerar o XML do DPS.

            // Vamos ver como a lib espera.
            // Tools::enviaDps($content) chama $this->sign($content, 'infDPS', '', 'DPS');
            // Então $content deve ser o XML da tag <DPS>...</DPS> ou <infDPS>...</infDPS>?
            // O sign() assina e retorna o XML assinado.
            // Depois enviaDps envelopa em <dpsXmlGZipB64>.

            // O Dps->render($std) constrói o DOM. Eu preciso pegar o XML dele.
            // Dps estende DpsInterface? Não vejo método saveXML ou getXML.
            // Dps.php usa NFePHP\Common\DOMImproved as Dom.
            // Preciso ver se Dps tem um método para retornar o XML.
            // Dps.php: render($std) popula $this->dom.
            // Mas $this->dom é protected.

            // Espere, vamos olhar Dps.php novamente.
            // public function render(stdClass $std = null) ... $this->dom->addChild(...)
            // Não retorna nada.

            // Se a classe Dps não retorna o XML, como usá-la?
            // Talvez eu tenha que acessar $dps->dom->saveXML()? Mas é protected.

            // Verificando Dps.php novamente...
            // Ah, se a lib é "feita a toque de caixa", talvez eu precise estender ou refletir.
            // OU, eu posso construir o XML na mão ou usar a classe se ela tiver um método __toString ou save().

            // Vamos assumir que eu tenho que usar Dps para gerar o XML.
            // Se Dps não tem método público para pegar o XML, isso é um problema da lib.
            // Vou checar se Dps herda algo que exponha o DOM. Não, DpsInterface?

            // Alternativa: Se a lib for difícil de usar, eu posso gerar o XML na mão (DOMDocument) seguindo o padrão.
            // Mas vamos tentar usar a lib.

            // Olhando Dps.php no arquivo lido anteriormente:
            // use NFePHP\Common\DOMImproved as Dom;
            // protected $dom;

            // Realmente, não tem getter. Que falha.
            // Mas espere, talvez o render retorne algo e eu perdi?
            // public function render(stdClass $std = null) { ... $this->dom->addChild(...) ... }
            // Não tem return.

            // Vou usar Reflection para pegar o XML do Dps object se necessário, ou construir meu próprio gerador de XML.
            // Reflection é mais rápido agora.

            $dpsGenerator = new Dps($std);
            $dpsGenerator->render($std);

            $reflection = new \ReflectionClass($dpsGenerator);
            $property = $reflection->getProperty('dom');
            $property->setAccessible(true);
            $dom = $property->getValue($dpsGenerator);

            // CORREÇÃO: Sobrescrever o atributo Id do infDPS com o valor calculado corretamente
            // A biblioteca gera um ID incorreto (fora do padrão SPED)
            if (isset($std->infDPS->Id)) {
                $infDpsElement = $dom->getElementsByTagName('infDPS')->item(0);
                if ($infDpsElement) {
                $infDpsElement->setAttribute('Id', $std->infDPS->Id);
                $infDpsElement->setIdAttribute('Id', true);
                 // Force namespace on infDPS to ensure consistency during C14N
                 $infDpsElement->setAttribute('xmlns', 'http://www.sped.fazenda.gov.br/nfse');
            }
        }

            $xmlDps = $dom->saveXML($dom->documentElement);

            // Log XML gerado para debug
            Log::info('NFSeService: XML DPS Gerado para envio', ['xml' => $xmlDps]);

            // O Tools::enviaDps espera o conteúdo para assinar.
            // O método sign do Tools assina o XML.
            // O enviaDps chama: $content = $this->sign($content, 'infDPS', '', 'DPS');
            // Então ele espera que $content tenha a tag <DPS><infDPS>...</infDPS></DPS> ?
            // O Dps->render cria <DPS><infDPS>...</infDPS></DPS>.
            // Então $xmlDps é o que precisamos.

            // Salva o XML gerado para debug
            $nfse->xml_envio = $xmlDps;
            $nfse->save();

            Log::info('NFSeService: Enviando DPS para API...');
            $response = $this->tools->enviaDps($xmlDps);
            Log::info('NFSeService: Resposta da API Recebida', ['response' => $response]);

            // 4. Processar retorno
            // O retorno é um array (json_decode da resposta).

            // Se sucesso, deve ter chave de acesso ou infDPS processado?
            // Exemplo de resposta de sucesso?

            if (isset($response['erro'])) {
                throw new Exception("Erro API: " . json_encode($response));
            }

            // Precisamos analisar a resposta para saber se foi autorizado
            // Normalmente retorna a chave de acesso ou protocolo.

            // Vamos salvar o retorno completo
            $nfse->xml_retorno = json_encode($response);

            // Analisa a resposta para atualizar o status
            $dadosRetorno = $response;
            if (isset($response['response'])) {
                $dadosRetorno = $response['response'];
            }

            if (isset($dadosRetorno['chaveAcesso'])) {
                $nfse->status = 'autorizada';
                $nfse->chave_acesso = $dadosRetorno['chaveAcesso'];

                // Link oficial para download
                $nfse->link_nfse = "https://www.nfse.gov.br/EmissorNacional/Notas/Download/DANFSe/" . $nfse->chave_acesso;

                // Salvar XML retornado (se disponível e válido)
                if (isset($dadosRetorno['nfseXmlGZipB64'])) {
                    try {
                        $xmlDecoded = gzdecode(base64_decode($dadosRetorno['nfseXmlGZipB64']));
                        if ($xmlDecoded) {
                            $nfse->xml_retorno = $xmlDecoded;
                        }
                    } catch (Exception $e) {
                        Log::warning('Erro ao decodificar XML da NFS-e: ' . $e->getMessage());
                    }
                }

                $nfse->save();

                // Atualiza o último número RPS utilizado nas configurações
                try {
                    Configuracao::set('nfse_ultimo_numero', $nfse->numero_rps, 'nfe', 'number');
                } catch (\Exception $e) {
                    Log::warning('Falha ao atualizar nfse_ultimo_numero: ' . $e->getMessage());
                }

                return ['status' => true, 'message' => 'NFS-e autorizada com sucesso!', 'data' => $response];
            }

            // Verifica erros na resposta interna
            if (isset($dadosRetorno['erros'])) {
                $erros = $dadosRetorno['erros'];
                $msgErro = "Erro na API: ";
                foreach ($erros as $erro) {
                    $msgErro .= "[{$erro['Codigo']}] {$erro['Descricao']} ";
                }
                throw new Exception($msgErro);
            }

            // Fallback para debug
            $nfse->save();

            return [
                'status' => true,
                'message' => 'NFS-e enviada. Verifique o retorno.',
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Erro na emissão de NFS-e Nacional: ' . $e->getMessage());
            $nfse->status = 'rejeitada';
            $nfse->motivo_rejeicao = $e->getMessage();
            $nfse->save();

            return [
                'status' => false,
                'message' => 'Exceção ao emitir: ' . $e->getMessage()
            ];
        }
    }

    protected function validarDados(NotaFiscalServico $nfse)
    {
        if (!$nfse->cliente) {
            throw new Exception("Cliente não associado à NFS-e.");
        }
        if (empty($nfse->cliente->cpf_cnpj)) {
            throw new Exception("CPF/CNPJ do cliente é obrigatório.");
        }
        if (empty($nfse->codigo_servico)) {
            throw new Exception("Código do Serviço (LC 116) é obrigatório.");
        }
    }

    protected function gerarDpsStd(NotaFiscalServico $nfse)
    {
        $std = new stdClass();
        $std->version = "1.00";

        $std->infDPS = new stdClass();
        $std->infDPS->tpAmb = $this->config['ambiente'];
        $std->infDPS->dhEmi = $nfse->created_at->format('Y-m-d\TH:i:sP');
        $std->infDPS->verAplic = "JBTECH_1.0";
        $std->infDPS->serie = $nfse->serie_rps ?? Configuracao::get('nfe_serie', '1'); // Usa a série da nota ou configuração

        // Determina o número do DPS (RPS)
        // Se numero_rps não estiver definido, usa o ID da nota como fallback
        $numeroRps = $nfse->numero_rps;
        if (empty($numeroRps)) {
            $numeroRps = $nfse->id;
        }

        // CORREÇÃO: O ID do DPS deve seguir o padrão "DPS" + Cód.Mun (7) + Tipo Insc (1) + CNPJ (14) + Série (5) + Número (15)
        // Total: 3 + 7 + 1 + 14 + 5 + 15 = 45 caracteres
        // Ref: tiposSimples_v1.00.xsd -> TSIdDPS

        $codMunicipio = str_pad(preg_replace('/[^0-9]/', '', $this->config['cod_municipio']), 7, '0', STR_PAD_LEFT);
        $tipoInscricao = '2'; // 2 = CNPJ, 1 = CPF (Padrão Nacional)
        $cnpjPrestador = str_pad(preg_replace('/[^0-9]/', '', $this->config['cnpj']), 14, '0', STR_PAD_LEFT);
        $serieDps = str_pad($std->infDPS->serie, 5, '0', STR_PAD_LEFT);
        $numeroDps = str_pad((string)$numeroRps, 15, '0', STR_PAD_LEFT);

        $std->infDPS->Id = "DPS" . $codMunicipio . $tipoInscricao . $cnpjPrestador . $serieDps . $numeroDps;

        // Log::info('NFSeService: Generated ID components', [
        //     'codMunicipio' => $codMunicipio,
        //     'tipoInscricao' => $tipoInscricao,
        //     'cnpjPrestador' => $cnpjPrestador,
        //     'serieDps' => $serieDps,
        //     'numeroDps' => $numeroDps,
        //     'FullID' => $std->infDPS->Id
        // ]);

        // Padroniza campos no XML para bater com o ID
        // nDPS e serie nas tags não podem ter zeros à esquerda para validação de tipo,
        // mas o ID deve ser calculado com zeros à esquerda.
        $std->infDPS->serie = (string)((int)$serieDps);
        $std->infDPS->nDPS = (string)$numeroRps;
        // Mas o atributo Id na tag infDPS precisa ser o ID completo formatado

        $std->infDPS->dCompet = $nfse->created_at->format('Y-m-d');
        $std->infDPS->tpEmit = 1; // 1=Prestador de serviço
        $std->infDPS->cLocEmi = preg_replace('/[^0-9]/', '', $this->config['cod_municipio']); // IBGE do local de emissão (Sanitized)

        // Prestador
        $std->infDPS->prest = new stdClass();
        $std->infDPS->prest->CNPJ = preg_replace('/[^0-9]/', '', $this->config['cnpj']);
        if (!empty($this->config['im'])) {
            $std->infDPS->prest->IM = preg_replace('/[^0-9]/', '', $this->config['im']);
        }

        // Regime Tributário
        $crt = Configuracao::get('nfe_crt', '1');

        // Inicializa objeto regTrib explicitamente
        $regTribObj = new stdClass();

        // Mapeia CRT para Opção Simples Nacional (1=Não Optante, 2=MEI, 3=ME/EPP)
        // CRT 1 (Simples) -> opSimpNac = 3 (Assume ME/EPP)
        // CRT 2 (Simples Excesso - Aqui usado como MEI no sistema?) -> opSimpNac = 2
        // CRT 3 (Normal) -> opSimpNac = 1 (Não Optante)

        $opSimpNac = 1; // Default
        if ($crt == '2') {
             $opSimpNac = 2; // MEI
        } elseif ($crt == '1') {
             $opSimpNac = 3; // Simples Nacional ME/EPP
        }

        $regTribObj->opSimpNac = $opSimpNac;
        $regTribObj->regEspTrib = 0; // 0 = Nenhum (Padrão)

        // Se for Optante Simples Nacional ME/EPP (3), exige Regime de Apuração
        if ($opSimpNac == 3) {
            // 1 - Regime de Caixa
            // 2 - Regime de Competência
            // Padrão: 1 (Caixa) - Pode ser transformado em configuração posteriormente
            $regTribObj->regApTribSN = 1;
        }

        $std->infDPS->prest->regTrib = $regTribObj;

        // Log::info('NFSeService: regTrib structure', ['regTrib' => $std->infDPS->prest->regTrib]);

        // Tomador
        $std->infDPS->toma = new stdClass();
        $cpfCnpjTomador = preg_replace('/[^0-9]/', '', $nfse->cliente->cpf_cnpj);

        if (strlen($cpfCnpjTomador) > 11) {
            $std->infDPS->toma->CNPJ = $cpfCnpjTomador;
        } else {
            $std->infDPS->toma->CPF = $cpfCnpjTomador;
        }
        $std->infDPS->toma->xNome = $nfse->cliente->nome;

        // Endereço do Tomador (Obrigatório para evitar caracterização de exportação indevida)
        if ($nfse->cliente->endereco) {
            $std->infDPS->toma->end = new stdClass();
            $std->infDPS->toma->end->endNac = new stdClass();

            // Tenta obter o código IBGE
            $cMun = $this->config['cod_municipio']; // Default para mesmo município
            // TODO: Implementar busca correta de código IBGE baseada no nome da cidade/estado se diferente
            // Por enquanto, assume mesmo município se nomes baterem ou fallback

            $std->infDPS->toma->end->endNac->cMun = $cMun;
            $std->infDPS->toma->end->endNac->CEP = preg_replace('/[^0-9]/', '', $nfse->cliente->endereco->cep);

            $std->infDPS->toma->end->xLgr = Str::limit($nfse->cliente->endereco->endereco, 60);
            $std->infDPS->toma->end->nro = Str::limit($nfse->cliente->endereco->numero ?? 'S/N', 10);
            $std->infDPS->toma->end->xBairro = Str::limit($nfse->cliente->endereco->bairro ?? 'Centro', 60);
            if (!empty($nfse->cliente->endereco->complemento)) {
                 $std->infDPS->toma->end->xCpl = Str::limit($nfse->cliente->endereco->complemento, 60);
            }
        }

        // Serviço
        $std->infDPS->serv = new stdClass();
        $std->infDPS->serv->cServ = new stdClass();
        // Remove caracteres não numéricos do código de tributação nacional (ex: 14.01 -> 1401)
        // Se tiver 4 dígitos (Item + Subitem), adiciona '01' como desdobro padrão
        // Se tiver 6 dígitos, mantém
        $cTribNacRaw = preg_replace('/[^0-9]/', '', $nfse->codigo_servico);
        if (strlen($cTribNacRaw) === 4) {
            $std->infDPS->serv->cServ->cTribNac = $cTribNacRaw . '01';
        } else {
            $std->infDPS->serv->cServ->cTribNac = str_pad($cTribNacRaw, 6, '0', STR_PAD_RIGHT);
        }

        // Log::info('NFSeService: cTribNac processed', [
        //     'original' => $nfse->codigo_servico,
        //     'cleaned' => $cTribNacRaw,
        //     'final' => $std->infDPS->serv->cServ->cTribNac
        // ]);

        // Código NBS (Nomenclatura Brasileira de Serviços) - Obrigatório se houver exportação ou conforme regra municipal
        if (!empty($nfse->codigo_nbs)) {
            $std->infDPS->serv->cServ->cNBS = preg_replace('/[^0-9]/', '', $nfse->codigo_nbs);
        }

        // $std->infDPS->serv->cServ->cTribMun = preg_replace('/[^0-9]/', '', $nfse->codigo_servico);
        // cTribMun (Código Tributação Municipal) deve ter 3 dígitos. Como codigo_servico é LC116 (ex: 14.01), não serve.
        // O campo é opcional (minOccurs=0), então vamos omitir para evitar erro de schema (RNG6110).

        $std->infDPS->serv->cServ->xDescServ = trim(preg_replace('/\s+/', ' ', $nfse->discriminacao ?? 'Serviço Prestado')); // Normalize whitespace

        $std->infDPS->serv->locPrest = new stdClass();
        $std->infDPS->serv->locPrest->cLocPrestacao = $nfse->municipio_prestacao ?? $this->config['cod_municipio'];

        // Valores
        $std->infDPS->valores = new stdClass();
        $std->infDPS->valores->vServPrest = new stdClass();
        $std->infDPS->valores->vServPrest->vServ = number_format($nfse->valor_servico, 2, '.', '');

        // Tributação Municipal (ISSQN)
        $std->infDPS->valores->trib = new stdClass();
        $std->infDPS->valores->trib->tribMun = new stdClass();

        // Mapeamento Tributação ISSQN
        // 1: Tributável
        // 2: Imunidade
        // 3: Exportação de serviço
        // 4: Não Incidência
        $tribIssqn = 1; // Padrão Normal

        // Se for MEI (opSimpNac = 2)
        if ($opSimpNac == 2) {
             $tribIssqn = 1; // MEI Tributável no Nacional (sem alíquota)
        }

        if ($nfse->iss_retido) {
            $tribIssqn = 1; // Se for retido, deve ser tributável?
        }
        $std->infDPS->valores->trib->tribMun->tribISSQN = $tribIssqn;

        // Tipo de Retenção do ISSQN (Obrigatório)
        // 1 - Não Retido
        // 2 - Retido pelo Tomador
        // 3 - Retido pelo Intermediário
        $std->infDPS->valores->trib->tribMun->tpRetISSQN = ($nfse->iss_retido) ? 2 : 1;

        // tpImunidade (Opcional)
        if ($tribIssqn == 2) {
             $std->infDPS->valores->trib->tribMun->tpImunidade = 0;
        }

        // Se tiver alíquota definida
        // Log::info('NFSeService: Checking aliquota', ['aliquota_iss' => $nfse->aliquota_iss]);
        if ($nfse->aliquota_iss > 0 && $opSimpNac != 2) {
            $std->infDPS->valores->trib->tribMun->pAliq = number_format($nfse->aliquota_iss, 2, '.', '');
        }

        // Totais Tributos (Obrigatório escolha de um tipo)
        $std->infDPS->valores->trib->totTrib = new stdClass();
        $std->infDPS->valores->trib->totTrib->indTotTrib = 0; // 0 - Não informar nenhum valor estimado (Lei 12.741/2012)

        return $std;
    }

    public function downloadPdf(NotaFiscalServico $nfse)
    {
        if (!$nfse->chave_acesso) {
            throw new Exception("NFS-e sem chave de acesso.");
        }

        if (!$this->tools) {
            if (!$this->certificate) $this->loadCertificate();
            $toolsConfig = json_encode(['tpamb' => $this->config['ambiente']]);
            $this->tools = new Tools($toolsConfig, $this->certificate);
        }

        // Tenta consultar DANFSe
        $pdfContent = $this->tools->consultarDanfse($nfse->chave_acesso);

        // Verifica se retornou erro
        if (is_array($pdfContent) && isset($pdfContent['erro'])) {
            throw new Exception("Erro ao baixar PDF: " . json_encode($pdfContent));
        }

        return $pdfContent; // Deve ser o binário do PDF
    }

    /**
     * Cancela uma NFS-e autorizada
     *
     * @param NotaFiscalServico $nfse
     * @param string $codigoMotivo Código numérico (1=Erro Emissão, 2=Serviço não prestado, etc)
     * @param string $descricaoMotivo Descrição detalhada (min 15 chars)
     * @return array Resposta da API
     */
    public function cancelar(NotaFiscalServico $nfse, $codigoMotivo, $descricaoMotivo)
    {
        if ($nfse->status !== 'autorizada' || !$nfse->chave_acesso) {
            throw new Exception("Apenas NFS-e autorizada pode ser cancelada.");
        }

        if (!$this->tools) {
            if (!$this->certificate) $this->loadCertificate();
            $toolsConfig = json_encode(['tpamb' => $this->config['ambiente']]);
            $this->tools = new Tools($toolsConfig, $this->certificate);
        }

        $std = new stdClass();
        $std->version = "1.00";
        $std->infPedReg = new stdClass();
        $std->infPedReg->tpAmb = $this->config['ambiente'];
        $std->infPedReg->dhEvento = date('Y-m-d\TH:i:sP');
        $std->infPedReg->verAplic = "JBTECH_1.0";

        // Autor do evento (Prestador)
        $std->infPedReg->CNPJAutor = preg_replace('/[^0-9]/', '', $this->config['cnpj']);

        $std->infPedReg->chNFSe = $nfse->chave_acesso;

        // Evento de Cancelamento (101101)
        $std->infPedReg->e101101 = new stdClass();
        $std->infPedReg->e101101->xDesc = "Cancelamento de NFS-e";
        $std->infPedReg->e101101->cMotivo = $codigoMotivo;
        $std->infPedReg->e101101->xMotivo = $descricaoMotivo;

        try {
             $response = $this->tools->cancelaNfse($std);

             if (isset($response['erro'])) {
                 throw new Exception("Erro API Cancelamento: " . json_encode($response));
             }

             return $response;

        } catch (Exception $e) {
            Log::error("Erro ao cancelar NFS-e {$nfse->id}: " . $e->getMessage());
            throw $e;
        }
    }

    // --- Métodos de Carregamento de Certificado (Duplicados de NFeService) ---

    protected function loadCertificate()
    {
        $certPathConfig = Configuracao::get('nfe_cert_path');
        $certPath = storage_path('app/certificates/' . ($certPathConfig ?: 'certificado.pfx'));
        $senha = Configuracao::get('nfe_cert_password');

        // Tenta restaurar do banco se não existir no disco (Correção para Heroku)
        if (!file_exists($certPath)) {
            $certData = Configuracao::get('nfe_cert_data');
            if ($certData) {
                try {
                    if (!file_exists(dirname($certPath))) {
                        mkdir(dirname($certPath), 0755, true);
                    }
                    file_put_contents($certPath, base64_decode($certData));
                    Log::info('Certificado restaurado do banco de dados para: ' . $certPath);
                } catch (\Exception $e) {
                    Log::error('Erro ao restaurar certificado do banco: ' . $e->getMessage());
                }
            }
        }

        if (!file_exists($certPath)) {
            // Tenta caminho antigo/alternativo se falhar
            $certPathAlt = Configuracao::get('nfe_certificado');
            if ($certPathAlt && file_exists(storage_path('app/' . $certPathAlt))) {
                $certPath = storage_path('app/' . $certPathAlt);
            } else {
                throw new Exception("Arquivo do certificado não encontrado em: $certPath");
            }
        }

        if (empty($senha)) {
            // Tenta nfe_senha como fallback
            $senha = Configuracao::get('nfe_senha');
            if (empty($senha)) {
                throw new Exception("Senha do certificado não configurada.");
            }
        }

        $certContent = file_get_contents($certPath);

        // Tenta ler o PFX
        try {
            $this->certificate = Certificate::readPfx($certContent, $senha);
        } catch (Exception $e) {
            throw new Exception("Erro ao ler PFX (senha incorreta ou formato inválido): " . $e->getMessage());
        }
    }

    protected function configurarOpenSSLLegacy()
    {
        $opensslConfigPath = storage_path('app/openssl_legacy.cnf');
        $opensslConfig = <<<'INI'
openssl_conf = openssl_init

[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect
legacy = legacy_sect

[default_sect]
activate = 1

[legacy_sect]
activate = 1
INI;

        if (!file_exists($opensslConfigPath)) {
            file_put_contents($opensslConfigPath, $opensslConfig);
        }

        putenv('OPENSSL_CONF=' . $opensslConfigPath);
        $_ENV['OPENSSL_CONF'] = $opensslConfigPath;
        $_SERVER['OPENSSL_CONF'] = $opensslConfigPath;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            putenv('OPENSSL_CONF=' . $opensslConfigPath);
        }

        $possibleModulePaths = [
            'C:\\xampp\\php\\extras\\ssl',
            'C:\\xampp\\apache\\lib\\ossl-modules',
            'C:\\Program Files\\Git\\mingw64\\lib\\ossl-modules',
            'C:\\Program Files\\Git\\usr\\lib\\ossl-modules',
            'C:\\OpenSSL-Win64\\lib\\ossl-modules',
            'C:\\OpenSSL-Win32\\lib\\ossl-modules',
        ];

        foreach ($possibleModulePaths as $path) {
            if (file_exists($path) && file_exists($path . '\\legacy.dll')) {
                putenv('OPENSSL_MODULES=' . $path);
                $_ENV['OPENSSL_MODULES'] = $path;
                $_SERVER['OPENSSL_MODULES'] = $path;
                break;
            }
        }
    }

    protected function encontrarOpenSSLService()
    {
        $possiveisCaminhos = [
            'C:\\xampp\\apache\\bin\\openssl.exe',
            'C:\\xampp\\php\\extras\\openssl\\openssl.exe',
            'C:\\Program Files\\Git\\mingw64\\bin\\openssl.exe',
            'C:\\Program Files\\Git\\usr\\bin\\openssl.exe',
            'C:\\Program Files\\Git\\bin\\openssl.exe',
            'C:\\OpenSSL-Win64\\bin\\openssl.exe',
            'C:\\OpenSSL-Win32\\bin\\openssl.exe',
            'openssl',
        ];

        foreach ($possiveisCaminhos as $caminho) {
            $output = [];
            $returnVar = 0;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $caminho !== 'openssl') {
                if (!file_exists($caminho)) continue;
            }
            exec('"' . $caminho . '" version 2>&1', $output, $returnVar);
            if ($returnVar === 0) return $caminho;
        }
        return null;
    }

    protected function converterCertificadoLegacyService($certPath, $senha)
    {
        $this->configurarOpenSSLLegacy();
        $opensslPath = $this->encontrarOpenSSLService();
        if (!$opensslPath) throw new Exception('OpenSSL não encontrado.');

        $certPath = str_replace('\\', '/', $certPath);
        $pemPath = $certPath . '.pem';
        $pfxPath = $certPath . '.converted.pfx';
        $senhaFile = storage_path('app/temp_senha_' . uniqid() . '.txt');
        file_put_contents($senhaFile, $senha);
        $senhaFile = str_replace('\\', '/', $senhaFile);

        try {
            $extraArgs = '';
            if (getenv('OPENSSL_MODULES')) {
                $extraArgs .= ' -provider-path "' . getenv('OPENSSL_MODULES') . '"';
            }

            $commandExtract = sprintf(
                '"%s" pkcs12 -in "%s" -out "%s" -nodes -legacy -passin file:"%s"%s 2>&1',
                $opensslPath,
                $certPath,
                $pemPath,
                $senhaFile,
                $extraArgs
            );

            exec($commandExtract, $output, $returnVar);

            if ($returnVar !== 0) {
                // Retry logic simplified for brevity
                throw new Exception("Erro ao converter PFX para PEM: " . implode("\n", $output));
            }

            $commandCreate = sprintf(
                '"%s" pkcs12 -export -in "%s" -out "%s" -legacy -passout file:"%s" -passin file:"%s"%s 2>&1',
                $opensslPath,
                $pemPath,
                $pfxPath,
                $senhaFile,
                $senhaFile,
                $extraArgs
            );

            exec($commandCreate, $output2, $returnVar2);

            if ($returnVar2 !== 0) {
                throw new Exception("Erro ao criar PFX legado: " . implode("\n", $output2));
            }
        } finally {
            if (file_exists($senhaFile)) unlink($senhaFile);
            if (file_exists($pemPath)) unlink($pemPath);
        }
    }
}
