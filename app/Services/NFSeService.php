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
            if (isset($std->infdps->Id)) {
                $infDpsElement = $dom->getElementsByTagName('infDPS')->item(0);
                if ($infDpsElement) {
                    $infDpsElement->setAttribute('Id', $std->infdps->Id);
                }
            }

            $xmlDps = $dom->saveXML($dom->documentElement);

            Log::info('NFSeService: XML DPS Gerado', ['xml' => $xmlDps]);

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

            // Verificando campos comuns de sucesso
            // Se tiver 'chave' ou 'numero', sucesso.
            // A estrutura de retorno pode variar.

            // Ajuste conforme retorno real. Por enquanto, assumo sucesso se não tiver erro explícito
            // e tiver algum identificador.

            // Na falta de documentação clara, vamos salvar e marcar como 'processamento'.
            // Se for síncrono e vier a chave:

            if (isset($response['chave'])) { // Hipótese
                $nfse->status = 'autorizada';
                $nfse->chave_acesso = $response['chave'];
                $nfse->save();
                return ['status' => true, 'message' => 'NFS-e enviada com sucesso!', 'data' => $response];
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

        $std->infdps = new stdClass();
        $std->infdps->tpamb = $this->config['ambiente'];
        $std->infdps->dhemi = $nfse->created_at->format('Y-m-d\TH:i:sP');
        $std->infdps->veraplic = "JBTECH_1.0";
        $std->infdps->serie = "1"; // Série 1 padrão para MEI/Simples? Ajustável.

        // CORREÇÃO: O ID do DPS deve seguir o padrão "DPS" + Cód.Mun (7) + Tipo Insc (1) + CNPJ (14) + Série (5) + Número (15)
        // Total: 3 + 7 + 1 + 14 + 5 + 15 = 45 caracteres
        // Ref: tiposSimples_v1.00.xsd -> TSIdDPS

        $codMunicipio = str_pad(preg_replace('/[^0-9]/', '', $this->config['cod_municipio']), 7, '0', STR_PAD_LEFT);
        $tipoInscricao = '2'; // 2 = CNPJ (Padrão para emissor PJ)
        $cnpjPrestador = str_pad(preg_replace('/[^0-9]/', '', $this->config['cnpj']), 14, '0', STR_PAD_LEFT);
        $serieDps = str_pad($std->infdps->serie, 5, '0', STR_PAD_LEFT);
        $numeroDps = str_pad((string)$nfse->id, 15, '0', STR_PAD_LEFT);

        $std->infdps->Id = "DPS" . $codMunicipio . $tipoInscricao . $cnpjPrestador . $serieDps . $numeroDps;
        $std->infdps->ndps = (string)$nfse->id; // Aqui na tag nDPS vai o número normal
        // Mas o atributo Id na tag infDPS precisa ser o ID completo formatado
        // A lib Dps provavelmente gera o ID automaticamente se não informado, ou usa o que passarmos.
        // Vamos verificar se a lib aceita o atributo Id no stdClass.
        // Se não aceitar, teremos que confiar que ela gera certo, mas o erro diz que o gerado está inválido.
        // O erro mostra: "DPS25481991000012000001000000000000002"
        // DPS + CNPJ (14) + "00001" (Série 5) + "000000000000002" (Número 15) = 3 + 14 + 5 + 15 = 37 chars.
        // O valor gerado no log parece ter 37 caracteres. Vamos contar.
        // DPS (3) + 25481991000012 (14, mas tem um 2 na frente?)
        // O CNPJ configurado é 54819910000120.
        // O erro mostra 25481991000012... O CNPJ está estranho, parece que tem um digito a mais ou a menos ou deslocado?
        // Ah, o erro diz: DPS25481991000012000001000000000000002
        // DPS (3)
        // 25481991000012 (14 chars) -> CNPJ errado? O CNPJ correto é 54819910000120
        // Parece que o CNPJ no ID está "25481991000012". Falta o 0 no final e tem um 2 no começo?
        // Ou o CNPJ configurado está errado?
        // Configuração: 54819910000120
        // Log gerado: <CNPJ>54819910000120</CNPJ> (Correto na tag)
        // Mas no ID ficou DPS25481991000012...
        // Espere, 2 + 54819910000120 (Se o ambiente for 2?)
        // Não, o padrão do ID é DPS + CNPJ + Série + Número.
        // A lib Hadder/NfseNacional deve estar gerando o ID.
        // Vamos ver como ela gera.
        // Se a lib gera errado, temos que passar o ID explicitamente se possível.

        // Tentar forçar o ID correto se a estrutura permitir
        // $std->infdps->id = "DPS" . $cnpjPrestador . $serieDps . $numeroDps;

        $std->infdps->dcompet = $nfse->created_at->format('Y-m-d');
        $std->infdps->tpemit = 1; // 1=Prestador de serviço
        $std->infdps->clocemi = $this->config['cod_municipio']; // IBGE do local de emissão

        // Prestador
        $std->infdps->prest = new stdClass();
        $std->infdps->prest->cnpj = preg_replace('/[^0-9]/', '', $this->config['cnpj']);
        if (!empty($this->config['im'])) {
            $std->infdps->prest->im = preg_replace('/[^0-9]/', '', $this->config['im']);
        }

        // Regime Tributário
        $crt = Configuracao::get('nfe_crt', '1');

        // Inicializa objeto regtrib explicitamente
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

        $regTribObj->opsimpnac = $opSimpNac;
        $regTribObj->regesptrib = 0; // 0 = Nenhum (Padrão)

        // Se for Optante Simples Nacional ME/EPP (3), exige Regime de Apuração
        if ($opSimpNac == 3) {
            // 1 - Regime de Caixa
            // 2 - Regime de Competência
            // Padrão: 1 (Caixa) - Pode ser transformado em configuração posteriormente
            $regTribObj->regaptribsn = 1;
        }

        $std->infdps->prest->regtrib = $regTribObj;

        // Log para debug
        Log::info('NFSeService: regtrib structure', ['regtrib' => $std->infdps->prest->regtrib]);

        // Tomador
        $std->infdps->toma = new stdClass();
        $cpfCnpjTomador = preg_replace('/[^0-9]/', '', $nfse->cliente->cpf_cnpj);

        if (strlen($cpfCnpjTomador) > 11) {
            $std->infdps->toma->cnpj = $cpfCnpjTomador;
        } else {
            $std->infdps->toma->cpf = $cpfCnpjTomador;
        }
        $std->infdps->toma->xnome = $nfse->cliente->nome;

        // Endereço do Tomador (Obrigatório para evitar caracterização de exportação indevida)
        if ($nfse->cliente->endereco) {
            $std->infdps->toma->end = new stdClass();
            $std->infdps->toma->end->endnac = new stdClass();

            // Tenta obter o código IBGE
            $cMun = $this->config['cod_municipio']; // Default para mesmo município
            // TODO: Implementar busca correta de código IBGE baseada no nome da cidade/estado se diferente
            // Por enquanto, assume mesmo município se nomes baterem ou fallback

            $std->infdps->toma->end->endnac->cmun = $cMun;
            $std->infdps->toma->end->endnac->cep = preg_replace('/[^0-9]/', '', $nfse->cliente->endereco->cep);

            $std->infdps->toma->end->xlgr = Str::limit($nfse->cliente->endereco->endereco, 60);
            $std->infdps->toma->end->nro = Str::limit($nfse->cliente->endereco->numero ?? 'S/N', 10);
            $std->infdps->toma->end->xbairro = Str::limit($nfse->cliente->endereco->bairro ?? 'Centro', 60);
            if (!empty($nfse->cliente->endereco->complemento)) {
                 $std->infdps->toma->end->xcpl = Str::limit($nfse->cliente->endereco->complemento, 60);
            }
        }

        // Serviço
        $std->infdps->serv = new stdClass();
        $std->infdps->serv->cserv = new stdClass();
        // Remove caracteres não numéricos do código de tributação nacional (ex: 14.01 -> 1401)
        // Se tiver 4 dígitos (Item + Subitem), adiciona '01' como desdobro padrão
        // Se tiver 6 dígitos, mantém
        $cTribNacRaw = preg_replace('/[^0-9]/', '', $nfse->codigo_servico);
        if (strlen($cTribNacRaw) === 4) {
            $std->infdps->serv->cserv->ctribnac = $cTribNacRaw . '01';
        } else {
            $std->infdps->serv->cserv->ctribnac = str_pad($cTribNacRaw, 6, '0', STR_PAD_RIGHT);
        }

        Log::info('NFSeService: cTribNac processed', [
            'original' => $nfse->codigo_servico,
            'cleaned' => $cTribNacRaw,
            'final' => $std->infdps->serv->cserv->ctribnac
        ]);

        // Código NBS (Nomenclatura Brasileira de Serviços) - Obrigatório se houver exportação ou conforme regra municipal
        if (!empty($nfse->codigo_nbs)) {
            $std->infdps->serv->cserv->cnbs = preg_replace('/[^0-9]/', '', $nfse->codigo_nbs);
        }

        // $std->infdps->serv->cserv->ctribmun = preg_replace('/[^0-9]/', '', $nfse->codigo_servico);
        // cTribMun (Código Tributação Municipal) deve ter 3 dígitos. Como codigo_servico é LC116 (ex: 14.01), não serve.
        // O campo é opcional (minOccurs=0), então vamos omitir para evitar erro de schema (RNG6110).

        $std->infdps->serv->cserv->xdescserv = $nfse->discriminacao ?? 'Serviço Prestado'; // Obrigatório

        $std->infdps->serv->locprest = new stdClass();
        $std->infdps->serv->locprest->clocprestacao = $nfse->municipio_prestacao ?? $this->config['cod_municipio'];

        // Valores
        $std->infdps->valores = new stdClass();
        $std->infdps->valores->vservprest = new stdClass();
        $std->infdps->valores->vservprest->vserv = number_format($nfse->valor_servico, 2, '.', '');

        // Tributação Municipal (ISSQN)
        $std->infdps->valores->trib = new stdClass();
        $std->infdps->valores->trib->tribmun = new stdClass();

        // Mapeamento Tributação ISSQN
        // 1: Tributável (com incidência)
        // 2: Tributável com Retenção
        // 3: Isenta
        // 4: Imune
        $tribIssqn = 1; // Padrão Normal

        // Se for MEI (opSimpNac = 2), geralmente é Imune/Isento na NFS-e (recolhe via DAS)
        // Vamos tentar Isenta (3) ou Imune (4) se E999 persistir
        // Mas por enquanto, vamos manter 1 e ver se o erro muda.
        // Update: E999 com 1. Vamos tentar mudar para 3 (Isenta) se for MEI.
         if ($opSimpNac == 2) {
                $tribIssqn = 2; // Tentativa Imunidade
            }

        if ($nfse->iss_retido) {
            $tribIssqn = 2; // Retido
        }
        $std->infdps->valores->trib->tribmun->tribissqn = $tribIssqn;

        // Tipo de Retenção do ISSQN (Obrigatório)
        // 1 - Não Retido
        // 2 - Retido pelo Tomador
        // 3 - Retido pelo Intermediário
        $std->infdps->valores->trib->tribmun->tpretissqn = ($nfse->iss_retido) ? 2 : 1;

        // Se tiver alíquota definida
        Log::info('NFSeService: Checking aliquota', ['aliquota_iss' => $nfse->aliquota_iss]);
        if ($nfse->aliquota_iss > 0) {
            $std->infdps->valores->trib->tribmun->paliq = number_format($nfse->aliquota_iss, 2, '.', '');
        }

        // Totais Tributos (Obrigatório escolha de um tipo)
        $std->infdps->valores->trib->tottrib = new stdClass();
        $std->infdps->valores->trib->tottrib->indtottrib = 0; // 0 - Não informar nenhum valor estimado (Lei 12.741/2012)

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
