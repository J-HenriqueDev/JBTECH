<?php

namespace App\Services;

use App\Models\NotaFiscal;
use App\Models\Venda;
use App\Models\Clientes;
use App\Models\Produto;
use App\Models\Configuracao;
use App\Models\NaturezaOperacao;
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
use Exception;

class NFeService
{
    protected $config;
    protected $certificate;
    protected $tools;

    public function __construct()
    {
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

    /**
     * Configura o OpenSSL para usar provider legacy (necessário para OpenSSL 3.x)
     */
    protected function configurarOpenSSLLegacy()
    {
        // Cria um arquivo de configuração temporário do OpenSSL
        $opensslConfigPath = storage_path('app/openssl_legacy.cnf');

        // Conteúdo do arquivo de configuração para ativar provider legacy
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

        // Cria o arquivo de configuração se não existir
        if (!file_exists($opensslConfigPath)) {
            file_put_contents($opensslConfigPath, $opensslConfig);
        }

        // Configura a variável de ambiente OPENSSL_CONF ANTES de qualquer uso do OpenSSL
        // Usa múltiplas formas para garantir que funcione
        putenv('OPENSSL_CONF=' . $opensslConfigPath);
        $_ENV['OPENSSL_CONF'] = $opensslConfigPath;
        $_SERVER['OPENSSL_CONF'] = $opensslConfigPath;

        // No Windows, também tenta configurar via variável de sistema
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            putenv('OPENSSL_CONF=' . $opensslConfigPath);
        }

        // Tenta localizar e configurar o diretório de módulos do OpenSSL (crítico para Windows/Git Bash)
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

        return $opensslConfigPath;
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
            '/usr/bin/openssl',
            '/usr/local/bin/openssl',
        ];

        foreach ($possiveisCaminhos as $caminho) {
            $output = [];
            $returnVar = 0;
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && $caminho !== 'openssl') {
                if (!file_exists($caminho)) {
                    continue;
                }
            }

            exec('"' . $caminho . '" version 2>&1', $output, $returnVar);
            if ($returnVar === 0) {
                return $caminho;
            }
        }

        $output = [];
        $returnVar = 0;
        exec('openssl version 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            return 'openssl';
        }

        return null;
    }

    protected function converterCertificadoLegacyService($certPath, $senha)
    {
        $this->configurarOpenSSLLegacy();

        $opensslPath = $this->encontrarOpenSSLService();
        if (!$opensslPath) {
            throw new Exception('OpenSSL não encontrado no sistema.');
        }

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

            $output = [];
            $returnVar = 0;
            exec($commandExtract, $output, $returnVar);

            if ($returnVar !== 0) {
                $outputStr = implode(' ', $output);

                if (str_contains($outputStr, 'Error reading password') || str_contains($outputStr, 'Error getting passwords')) {
                    $senhaEscaped = str_replace('"', '\"', $senha);
                    $commandExtract = sprintf(
                        '"%s" pkcs12 -in "%s" -out "%s" -nodes -legacy -passin pass:"%s"%s 2>&1',
                        $opensslPath,
                        $certPath,
                        $pemPath,
                        $senhaEscaped,
                        $extraArgs
                    );
                    $output = [];
                    $returnVar = 0;
                    exec($commandExtract, $output, $returnVar);
                    $outputStr = implode(' ', $output);
                }

                if ($returnVar !== 0 && (str_contains($outputStr, 'unknown option') || str_contains($outputStr, 'bad flag'))) {
                    $commandExtract = sprintf(
                        '"%s" pkcs12 -in "%s" -out "%s" -nodes -passin file:"%s" 2>&1',
                        $opensslPath,
                        $certPath,
                        $pemPath,
                        $senhaFile
                    );
                    $output = [];
                    $returnVar = 0;
                    exec($commandExtract, $output, $returnVar);
                    $outputStr = implode(' ', $output);

                    if ($returnVar !== 0 && (str_contains($outputStr, 'Error reading password') || str_contains($outputStr, 'Error getting passwords'))) {
                        $senhaEscaped = str_replace('"', '\"', $senha);
                        $commandExtract = sprintf(
                            '"%s" pkcs12 -in "%s" -out "%s" -nodes -passin pass:"%s" 2>&1',
                            $opensslPath,
                            $certPath,
                            $pemPath,
                            $senhaEscaped
                        );
                        $output = [];
                        $returnVar = 0;
                        exec($commandExtract, $output, $returnVar);
                        $outputStr = implode(' ', $output);
                    }
                }

                if ($returnVar !== 0) {
                    if (str_contains($outputStr, 'Mac verify error') || str_contains($outputStr, 'invalid password')) {
                        throw new Exception('Senha do certificado incorreta (Validação CLI).');
                    }
                    throw new Exception('Falha na extração do certificado: ' . $outputStr);
                }
            }

            if (!file_exists($pemPath) || filesize($pemPath) === 0) {
                throw new Exception('Arquivo PEM temporário não foi criado ou está vazio.');
            }

            $commandExport = sprintf(
                '"%s" pkcs12 -export -in "%s" -out "%s" -passout file:"%s" 2>&1',
                $opensslPath,
                $pemPath,
                $pfxPath,
                $senhaFile
            );

            $output = [];
            $returnVar = 0;
            exec($commandExport, $output, $returnVar);

            if ($returnVar !== 0) {
                $outputStr = implode(' ', $output);
                if (str_contains($outputStr, 'Error reading password') || str_contains($outputStr, 'Error getting passwords')) {
                    $senhaEscaped = str_replace('"', '\"', $senha);
                    $commandExport = sprintf(
                        '"%s" pkcs12 -export -in "%s" -out "%s" -passout pass:"%s" 2>&1',
                        $opensslPath,
                        $pemPath,
                        $pfxPath,
                        $senhaEscaped
                    );
                    exec($commandExport, $output, $returnVar);
                }
            }

            if ($returnVar !== 0) {
                throw new Exception('Falha na geração do novo PFX: ' . implode(' ', $output));
            }

            if (file_exists($pfxPath) && filesize($pfxPath) > 0) {
                if (copy($pfxPath, $certPath)) {
                    @unlink($pfxPath);
                    @unlink($pemPath);
                    @unlink($senhaFile);
                    return true;
                }
            }

            throw new Exception('Arquivo convertido final não foi criado.');
        } catch (\Exception $e) {
            @unlink($senhaFile);
            if (isset($pemPath) && file_exists($pemPath)) {
                @unlink($pemPath);
            }
            if (isset($pfxPath) && file_exists($pfxPath)) {
                @unlink($pfxPath);
            }
            throw $e;
        }
    }

    protected function loadCertificate()
    {
        try {
            $certPathConfig = Configuracao::get('nfe_cert_path');
            $certPath = storage_path('app/certificates/' . ($certPathConfig ?: 'certificado.pfx'));

            $certPassword = Configuracao::get('nfe_cert_password', null, null);

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
                throw new Exception('Certificado digital não encontrado em: ' . $certPath . '. Por favor, faça o upload do certificado nas configurações.');
            }

            if (empty($certPassword)) {
                $debugPassword = Configuracao::get('nfe_cert_password');
                Log::error('Senha do certificado vazia ou nula no banco de dados', [
                    'retorno_explicito' => $certPassword,
                    'retorno_padrao' => $debugPassword,
                ]);
                throw new Exception('Senha do certificado digital não configurada. Por favor, configure a senha nas configurações do sistema (Configurações → NF-e).');
            }

            $certContent = file_get_contents($certPath);

            $this->configurarOpenSSLLegacy();

            if (function_exists('openssl_pkcs12_read')) {
                $certData = null;
                $result = @openssl_pkcs12_read($certContent, $certData, $certPassword);
                if ($result && isset($certData['cert'])) {
                }
            }

            try {
                $this->certificate = Certificate::readPfx($certContent, $certPassword);
                $this->tools = new Tools(json_encode($this->config), $this->certificate);
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();

                if (str_contains($errorMsg, 'digital envelope') || str_contains($errorMsg, 'unsupported')) {
                    $this->configurarOpenSSLLegacy();

                    try {
                        $this->converterCertificadoLegacyService($certPath, $certPassword);
                        $certContent = file_get_contents($certPath);
                        $this->certificate = Certificate::readPfx($certContent, $certPassword);
                        $this->tools = new Tools(json_encode($this->config), $this->certificate);
                        return;
                    } catch (\Exception $convEx) {
                        if (str_contains($convEx->getMessage(), 'Senha')) {
                            throw $convEx;
                        }

                        if (function_exists('openssl_pkcs12_read')) {
                            $certData = null;
                            $result = @openssl_pkcs12_read($certContent, $certData, $certPassword);
                            if ($result && isset($certData['cert'])) {
                                throw new Exception('O certificado foi lido pelo OpenSSL, mas a biblioteca NFePHP falhou. Verifique se a senha contém caracteres especiais ou se o certificado está corrompido.');
                            }
                        }

                        throw new Exception('Erro de compatibilidade com o certificado após tentativa de conversão: ' . $convEx->getMessage());
                    }
                }

                throw $e;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'mac verify failure') || str_contains($errorMessage, 'PKCS12_parse')) {
                $errorMessage = 'Senha do certificado digital incorreta. Por favor, verifique a senha nas configurações do sistema.';
            } elseif (str_contains($errorMessage, 'digital envelope') || str_contains($errorMessage, 'unsupported')) {
                $errorMessage = 'Erro de compatibilidade com o certificado. O certificado pode estar em um formato não suportado pela versão do OpenSSL/PHP. Tente exportar o certificado novamente ou verifique a versão do OpenSSL.';
            } elseif (str_contains($errorMessage, 'not found') || str_contains($errorMessage, 'não encontrado')) {
                $errorMessage = 'Certificado digital não encontrado. Por favor, faça o upload do certificado nas configurações.';
            }

            Log::error('Erro ao carregar certificado digital: ' . $e->getMessage());

            throw new Exception($errorMessage);
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

            // O objeto Certificate do NFePHP já expõe métodos para obter datas
            // mas vamos garantir que estamos pegando corretamente

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
            // Se der erro ao carregar (ex: senha errada), retorna erro
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
            // Configura OpenSSL para usar provider legacy (OpenSSL 3.x)
            $this->configurarOpenSSLLegacy();

            // Verifica se o certificado está carregado
            if (!$this->tools) {
                $this->loadCertificate();
            }

            // Verifica se a nota tem chave de acesso
            if (empty($notaFiscal->chave_acesso)) {
                throw new Exception('Esta nota fiscal não possui chave de acesso para consulta.');
            }

            // Realiza a consulta
            $chave = $notaFiscal->chave_acesso;
            $response = $this->tools->sefazConsultaChave($chave);

            // Analisa a resposta
            $stdCl = new Standardize($response);
            $std = $stdCl->toStd();

            // Verifica o status retornado pela SEFAZ
            if (isset($std->protNFe->infProt->cStat)) {
                $cStat = $std->protNFe->infProt->cStat;
                $xMotivo = $std->protNFe->infProt->xMotivo;

                if ($cStat == '100') {
                    $notaFiscal->status = 'autorizada';
                    $notaFiscal->motivo_rejeicao = null;

                    // Se temos o protocolo, anexamos ao XML se ele existir e ainda não estiver protocolado
                    if (isset($std->protNFe) && !empty($notaFiscal->xml)) {
                        $xmlAtual = $notaFiscal->xml;

                        // Evita tentar anexar protocolo em XML que já é procNFe/NFeProc
                        if (stripos($xmlAtual, '<nfeProc') === false && stripos($xmlAtual, '<NFeProc') === false) {
                            try {
                                $xmlProtocolado = Complements::toAuthorize($xmlAtual, $response);
                                $notaFiscal->xml = $xmlProtocolado;
                            } catch (\Exception $e) {
                                Log::warning("Erro ao anexar protocolo ao XML na consulta: " . $e->getMessage());
                            }
                        }
                    }

                    // Atualiza campos de protocolo se disponíveis
                    if (isset($std->protNFe->infProt->nProt)) {
                        $notaFiscal->protocolo = $std->protNFe->infProt->nProt;
                    }
                } elseif (in_array($cStat, ['101', '151', '155'])) { // Cancelada
                    $notaFiscal->status = 'cancelada';
                } elseif (in_array($cStat, ['110', '301', '302'])) { // Denegada
                    $notaFiscal->status = 'denegada';
                }

                $notaFiscal->save();

                // Registra log
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
            // Verifica se já existe NF-e para esta venda
            $notaExistente = NotaFiscal::where('venda_id', $venda->id)
                ->where('status', 'autorizada')
                ->first();

            if ($notaExistente) {
                throw new Exception('Já existe uma NF-e autorizada para esta venda.');
            }

            // Carrega os dados necessários
            $venda->load(['cliente.endereco', 'produtos', 'user']);
            $cliente = $venda->cliente;

            if (!$cliente->endereco) {
                throw new Exception('Cliente não possui endereço cadastrado.');
            }

            // Dados do emitente (empresa)
            $emitente = $this->getDadosEmitente();

            // Dados do destinatário (cliente)
            $destinatario = $this->getDadosDestinatario($cliente);

            // Produtos
            $produtos = [];
            $itemIndex = 1;
            foreach ($venda->produtos as $produto) {
                $produtos[] = $this->getDadosProduto($produto, $venda, $itemIndex++);
            }

            $naturezaPadrao = NaturezaOperacao::where('tipo', 'saida')
                ->where('padrao', true)
                ->first();

            $naturezaDescricao = $naturezaPadrao?->descricao ?? 'VENDA DE MERCADORIA';
            $finalidade = $naturezaPadrao?->finNFe ?? 1;

            // Prepara observações com ID da Venda e Nome do Vendedor
            $vendedor = $venda->user->name ?? 'N/A';
            $observacoes = "Venda: #{$venda->id} - Vendedor: {$vendedor}";

            // Adiciona observações da venda se houver
            if (!empty($venda->observacoes)) {
                $observacoes .= " | Obs: " . $venda->observacoes;
            }

            $notaFiscal = NotaFiscal::create([
                'venda_id' => $venda->id,
                'cliente_id' => $venda->cliente_id,
                'numero_nfe' => null,
                'chave_acesso' => null,
                'status' => 'pendente',
                'valor_total' => $venda->valor_total,
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

    /**
     * Cria uma NF-e Avulsa (Sem venda vinculada)
     */
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

            // Processa Produtos
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

            // Obtém nome do usuário atual (Vendedor)
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

    /**
     * Gera o PDF (DANFE) da NF-e usando o layout original do NFePHP
     */
    public function gerarPdf(NotaFiscal $notaFiscal)
    {
        try {
            $xml = $notaFiscal->xml;

            if (empty($xml)) {
                $xml = $this->montarXml($notaFiscal);
            }

            $danfe = new CustomDanfe($xml);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('Powered by JBTech');
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

    /**
     * Monta o XML da NF-e (sem assinar)
     */
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
        $nfeNumero = $notaFiscal->numero_nfe ?: $this->getNextNfeNumber();

        // Atualiza número se ainda não tiver (para garantir consistência na visualização)
        if (!$notaFiscal->numero_nfe) {
            $notaFiscal->numero_nfe = $nfeNumero;
            $notaFiscal->serie = $serie;
            // Não salva no banco aqui para não "gastar" o número apenas visualizando,
            // mas o ideal seria reservar. No contexto de montarXml para visualização,
            // usamos o próximo número apenas em memória se for rascunho.
        }

        $emitente = $this->getDadosEmitente();

        // Validação básica do endereço do emitente
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

        // Passamos endereço também no emit, mas vamos chamar tagenderEmit explicitamente
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

        // Data de Saída/Entrada (se informada)
        if (!empty($notaFiscal->data_saida)) {
            $stdIde->dhSaiEnt = $notaFiscal->data_saida->format('Y-m-d\TH:i:sP');
        }

        $stdIde->tpNF = $notaFiscal->tipo_documento ?? 1;

        // Lógica de idDest (1=Interna, 2=Interestadual, 3=Exterior)
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

        // FIX: Adicionar tagenderEmit explicitamente para corrigir erro de validação XML
        // "Element '{...}IE': This element is not expected. Expected is ({...}enderEmit)"
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

        // 3. Destinatário (tagdest/enderDest)
        if (empty($notaFiscal->dados_destinatario)) {
            $stdDest = new \stdClass();
            $stdDest->xNome = 'CLIENTE NÃO INFORMADO (VISUALIZAÇÃO)';
            $stdDest->indIEDest = '9';
            $stdDest->xLgr = 'Logradouro não informado';
            $stdDest->nro = 'S/N';
            $stdDest->xBairro = 'Bairro';
            $stdDest->cMun = '3304557';
            $stdDest->xMun = 'Rio de Janeiro';
            $stdDest->UF = 'RJ';
            $stdDest->CEP = '20000000';
            $stdDest->cPais = '1058';
            $stdDest->xPais = 'BRASIL';
            $make->tagdest($stdDest);

            $stdEnderDest = new \stdClass();
            $stdEnderDest->xLgr = $stdDest->xLgr;
            $stdEnderDest->nro = $stdDest->nro;
            $stdEnderDest->xCpl = '';
            $stdEnderDest->xBairro = $stdDest->xBairro;
            $stdEnderDest->cMun = $stdDest->cMun;
            $stdEnderDest->xMun = $stdDest->xMun;
            $stdEnderDest->UF = $stdDest->UF;
            $stdEnderDest->CEP = $stdDest->CEP;
            $stdEnderDest->cPais = $stdDest->cPais;
            $stdEnderDest->xPais = $stdDest->xPais;
            $make->tagenderDest($stdEnderDest);
        } else {
            $destinatario = (object) $notaFiscal->dados_destinatario;
            $stdDest = new \stdClass();
            $stdDest->xNome = $destinatario->xNome ?? 'CLIENTE SEM NOME';

            // Verifica se é CPF ou CNPJ
            if (strlen($destinatario->cpf_cnpj ?? '') > 11) {
                $stdDest->CNPJ = preg_replace('/[^0-9]/', '', $destinatario->cpf_cnpj);
            } else {
                $stdDest->CPF = preg_replace('/[^0-9]/', '', $destinatario->cpf_cnpj ?? '');
            }

            \Illuminate\Support\Facades\Log::info('NFeService Debug Destinatario:', [
                'IE_Original' => $destinatario->IE ?? 'null',
                'IE_Limpa' => preg_replace('/[^0-9]/', '', $destinatario->IE ?? ''),
                'UF_Original' => $destinatario->UF ?? 'null',
                'UF_Usada' => $destinatario->UF ?? 'RJ'
            ]);

            $ieRaw = isset($destinatario->IE) ? trim((string)$destinatario->IE) : '';
            $ieUpper = strtoupper($ieRaw);
            $docDest = preg_replace('/[^0-9]/', '', $destinatario->cpf_cnpj ?? '');

            if ($ieUpper === 'ISENTO') {
                $stdDest->IE = 'ISENTO';
                $stdDest->indIEDest = 2;
            } elseif ($ieRaw !== '') {
                $ieDigits = preg_replace('/[^0-9]/', '', $ieRaw);
                if ($ieDigits !== '') {
                    $stdDest->IE = $ieDigits;
                    $stdDest->indIEDest = 1;
                } else {
                    if (strlen($docDest) > 11) {
                        $stdDest->indIEDest = 2;
                    } else {
                        $stdDest->indIEDest = 9;
                    }
                }
            } else {
                if (strlen($docDest) > 11) {
                    $stdDest->IE = 'ISENTO';
                    $stdDest->indIEDest = 2;
                } else {
                    $stdDest->indIEDest = 9;
                }
            }

            if (!empty($destinatario->email)) $stdDest->email = $destinatario->email;

            $stdDest->xLgr = $destinatario->xLgr ?? 'Rua Principal';
            $stdDest->nro = $destinatario->nro ?? 'S/N';
            $stdDest->xCpl = $destinatario->xCpl ?? '';
            $cepDest = preg_replace('/\D/', '', (string) ($destinatario->CEP ?? ''));
            $stdDest->xBairro = $destinatario->xBairro ?? 'Centro';
            $stdDest->cMun = $destinatario->cMun ?? '3304557';
            $stdDest->xMun = $destinatario->xMun ?? 'Rio de Janeiro';
            $stdDest->UF = $destinatario->UF ?? 'RJ';
            $stdDest->CEP = $cepDest !== '' ? $cepDest : '20000000';
            $stdDest->cPais = '1058';
            $stdDest->xPais = 'BRASIL';

            $make->tagdest($stdDest);

            $stdEnderDest = new \stdClass();
            $stdEnderDest->xLgr = $stdDest->xLgr;
            $stdEnderDest->nro = $stdDest->nro;
            $stdEnderDest->xCpl = $stdDest->xCpl;
            $stdEnderDest->xBairro = $stdDest->xBairro;
            $stdEnderDest->cMun = $stdDest->cMun;
            $stdEnderDest->xMun = $stdDest->xMun;
            $stdEnderDest->UF = $stdDest->UF;
            $stdEnderDest->CEP = $stdDest->CEP;
            $stdEnderDest->cPais = $stdDest->cPais;
            $stdEnderDest->xPais = $stdDest->xPais;
            $make->tagenderDest($stdEnderDest);
        }

        // Produtos
        foreach ($notaFiscal->produtos as $index => $produto) {
            // Garante que o item está correto
            if (!isset($produto['item'])) {
                $produto['item'] = $index + 1;
            }

            // Garante Unidades e Valores obrigatórios
            $uCom = trim($produto['uCom'] ?? '');
            $produto['uCom'] = !empty($uCom) ? $uCom : 'UN';

            $produto['qCom'] = !empty($produto['qCom']) ? $produto['qCom'] : 1;
            $produto['vUnCom'] = !empty($produto['vUnCom']) ? $produto['vUnCom'] : 0;
            $produto['vProd'] = !empty($produto['vProd']) ? $produto['vProd'] : 0;

            // Unidade Tributável (Se vazio, usa Comercial)
            $uTrib = trim($produto['uTrib'] ?? '');
            $produto['uTrib'] = !empty($uTrib) ? $uTrib : $produto['uCom'];

            $produto['qTrib'] = !empty($produto['qTrib']) ? $produto['qTrib'] : $produto['qCom'];
            $produto['vUnTrib'] = !empty($produto['vUnTrib']) ? $produto['vUnTrib'] : $produto['vUnCom'];

            // Validação e Ajuste de GTIN/EAN (Evita rejeição 611 e 885)
            $validarGTIN = function ($ean) {
                // LOG DEBUG
                $eanOriginal = $ean;

                if (empty($ean) || strtoupper($ean) === 'SEM GTIN') return 'SEM GTIN';
                $ean = preg_replace('/[^0-9]/', '', (string)$ean);

                // Valida comprimento (8, 12, 13, 14)
                if (!in_array(strlen($ean), [8, 12, 13, 14])) {
                    \Illuminate\Support\Facades\Log::info("GTIN Inválido (Tamanho): '$eanOriginal' -> SEM GTIN");
                    return 'SEM GTIN';
                }

                // Bloqueia sequências repetidas (ex: 0000000000000)
                if (preg_match('/^(\d)\1*$/', $ean)) {
                    \Illuminate\Support\Facades\Log::info("GTIN Inválido (Repetido): '$eanOriginal' -> SEM GTIN");
                    return 'SEM GTIN';
                }

                // Validação de Dígito Verificador (Checksum)
                $soma = 0;
                $fator = 3;
                // Percorre do penúltimo dígito até o primeiro (direita para esquerda)
                for ($i = strlen($ean) - 2; $i >= 0; $i--) {
                    $soma += (int)$ean[$i] * $fator;
                    $fator = ($fator == 3) ? 1 : 3;
                }
                $digitoCalculado = (10 - ($soma % 10)) % 10;

                // Se o dígito verificador não bater, é inválido -> SEM GTIN
                if ($digitoCalculado != (int)$ean[strlen($ean) - 1]) {
                    \Illuminate\Support\Facades\Log::warning("GTIN Inválido (Checksum): '$eanOriginal' -> Forçando SEM GTIN");
                    return 'SEM GTIN';
                }

                return $ean;
            };

            $produto['cEAN'] = $validarGTIN($produto['cEAN'] ?? '');
            $produto['cEANTrib'] = $validarGTIN($produto['cEANTrib'] ?? '');

            // Garante consistência (Se tem EAN comercial, usa no tributável)
            if ($produto['cEAN'] !== 'SEM GTIN' && $produto['cEANTrib'] === 'SEM GTIN') {
                $produto['cEANTrib'] = $produto['cEAN'];
            }

            $make->tagprod((object)$produto);

            // Impostos (Simplificado - Simples Nacional)
            $std = new \stdClass();
            $std->item = $produto['item']; // Item index
            $std->orig = $produto['orig'] ?? 0;
            $std->CSOSN = $produto['CSOSN'] ?? '102';
            $std->pCredSN = $produto['pCredSN'] ?? null;
            $std->vCredICMSSN = $produto['vCredICMSSN'] ?? null;
            $std->modBCST = $produto['modBCST'] ?? null;
            $std->pMVAST = $produto['pMVAST'] ?? null;
            $std->pRedBCST = $produto['pRedBCST'] ?? null;
            $std->vBCST = $produto['vBCST'] ?? null;
            $std->pICMSST = $produto['pICMSST'] ?? null;

            $make->tagICMSSN($std);

            // PIS
            $std = new \stdClass();
            $std->item = $produto['item'];
            $std->CST = $produto['cst_pis'] ?? '99';
            $std->vBC = $produto['vProd'] ?? 0;
            $std->pPIS = 0;
            $std->vPIS = 0;
            $make->tagPIS($std);

            // COFINS
            $std = new \stdClass();
            $std->item = $produto['item'];
            $std->CST = $produto['cst_cofins'] ?? '99';
            $std->vBC = $produto['vProd'] ?? 0;
            $std->pCOFINS = 0;
            $std->vCOFINS = 0;
            $make->tagCOFINS($std);
        }

        // Totais
        $make->tagICMSTot((object)$this->calcularTotais($notaFiscal->produtos));

        // Transporte (Sem frete)
        $stdTransp = new \stdClass();
        $stdTransp->modFrete = 9;
        $make->tagtransp($stdTransp);

        // Pagamento
        $stdPag = new \stdClass();
        $stdPag->vTroco = null;
        $make->tagpag($stdPag);

        $pagamento = $notaFiscal->dados_pagamento;
        $stdDetPag = new \stdClass();
        $stdDetPag->tPag = $pagamento['forma'] ?? '01'; // Default: 01 - Dinheiro

        // Correção para Rejeição 904: Se tPag=90 (Sem Pagamento), vPag deve ser 0
        if ($stdDetPag->tPag == '90') {
            $stdDetPag->vPag = 0.00;
        } else {
            $stdDetPag->vPag = $notaFiscal->valor_total;
        }

        if (isset($pagamento['indicador'])) {
            $stdDetPag->indPag = $pagamento['indicador']; // 0=Vista, 1=Prazo
        }

        $make->tagdetpag($stdDetPag);

        if (is_array($pagamento) && !empty($pagamento['parcelas']) && is_array($pagamento['parcelas'])) {
            $stdFat = new \stdClass();
            $stdFat->nFat = $notaFiscal->numero_nfe;
            $stdFat->vOrig = $notaFiscal->valor_total;
            $stdFat->vLiq = $notaFiscal->valor_total;
            $make->tagfat($stdFat);

            $indice = 1;
            foreach ($pagamento['parcelas'] as $parcela) {
                if (empty($parcela['data']) || empty($parcela['valor'])) {
                    continue;
                }
                $stdDup = new \stdClass();
                $stdDup->nDup = str_pad($indice, 3, '0', STR_PAD_LEFT);
                $stdDup->dVenc = $parcela['data'];
                $stdDup->vDup = (float)$parcela['valor'];
                $make->tagdup($stdDup);
                $indice++;
            }
        } elseif (isset($pagamento['indicador']) && $pagamento['indicador'] == 1 && !empty($pagamento['qtd_parcelas']) && $pagamento['qtd_parcelas'] > 0) {
            // Geração automática de duplicatas com intervalo de 7 dias
            $stdFat = new \stdClass();
            $stdFat->nFat = $notaFiscal->numero_nfe;
            $stdFat->vOrig = $notaFiscal->valor_total;
            $stdFat->vLiq = $notaFiscal->valor_total;
            $make->tagfat($stdFat);

            $qtd = (int)$pagamento['qtd_parcelas'];
            $valorTotal = $notaFiscal->valor_total;
            $valorParcela = floor(($valorTotal / $qtd) * 100) / 100;
            $diferenca = $valorTotal - ($valorParcela * $qtd);

            for ($i = 1; $i <= $qtd; $i++) {
                $stdDup = new \stdClass();
                $stdDup->nDup = str_pad($i, 3, '0', STR_PAD_LEFT);

                $daysToAdd = $i * 7;
                $stdDup->dVenc = date('Y-m-d', strtotime("+{$daysToAdd} days"));

                $valor = $valorParcela;
                if ($i == $qtd) {
                    $valor += $diferenca;
                }
                $stdDup->vDup = $valor;

                $make->tagdup($stdDup);
            }
        } elseif (isset($pagamento['indicador']) && $pagamento['indicador'] == 1 && !empty($pagamento['dias'])) {
            $stdFat = new \stdClass();
            $stdFat->nFat = $notaFiscal->numero_nfe;
            $stdFat->vOrig = $notaFiscal->valor_total;
            $stdFat->vLiq = $notaFiscal->valor_total;
            $make->tagfat($stdFat);

            $stdDup = new \stdClass();
            $stdDup->nDup = "001";
            $stdDup->dVenc = date('Y-m-d', strtotime("+{$pagamento['dias']} days"));
            $stdDup->vDup = $notaFiscal->valor_total;
            $make->tagdup($stdDup);
        }

        // Informações Adicionais (Observações)
        // SEMPRE gera a tag infAdic para evitar erro "Call to a member function getElementsByTagName() on null"
        // na classe Danfe.php quando a tag não existe no XML.
        $stdAdic = new \stdClass();
        $stdAdic->infCpl = $notaFiscal->observacoes ?? null;
        $make->taginfAdic($stdAdic);

        return $make->getXML();
    }

    /**
     * Valida os requisitos obrigatórios para emissão da NF-e
     */
    public function validarRequisitosEmissao(NotaFiscal $nf)
    {
        // 1. Emitente
        $emitente = $this->getDadosEmitente();
        if (empty($emitente->CNPJ)) throw new Exception("Emitente: CNPJ não configurado nas configurações.");

        $ieEmit = isset($emitente->IE) ? trim((string) $emitente->IE) : '';
        if (empty($ieEmit)) throw new Exception("Emitente: Inscrição Estadual (IE) obrigatória. Configure em Configurações > NF-e.");

        // Valida formato IE (apenas números ou ISENTO)
        if (strcasecmp($ieEmit, 'ISENTO') !== 0 && !preg_match('/^[0-9]{2,14}$/', preg_replace('/\D/', '', $ieEmit))) {
            throw new Exception("Emitente: IE inválida ($ieEmit). Deve conter apenas números ou 'ISENTO'.");
        }

        $missingEmitente = [];
        if (empty($emitente->xLgr)) $missingEmitente[] = 'Logradouro';
        if (empty($emitente->nro)) $missingEmitente[] = 'Número';
        if (empty($emitente->xBairro)) $missingEmitente[] = 'Bairro';
        if (empty($emitente->xMun)) $missingEmitente[] = 'Município';
        if (empty($emitente->UF)) $missingEmitente[] = 'UF';

        if (!empty($missingEmitente)) {
            throw new Exception('Emitente: Endereço incompleto. Faltando: ' . implode(', ', $missingEmitente) . '. Configure em Configurações > NF-e.');
        }

        // 2. Destinatário
        if (empty($nf->dados_destinatario)) throw new Exception("Destinatário: Dados não informados na nota.");
        $dest = (object)$nf->dados_destinatario;

        if (empty($dest->cpf_cnpj)) throw new Exception("Destinatário: CPF/CNPJ obrigatório.");
        if (empty($dest->xNome)) throw new Exception("Destinatário: Nome/Razão Social obrigatório.");

        // Endereço do destinatário
        if (empty($dest->xLgr) || empty($dest->nro) || empty($dest->xBairro) || empty($dest->cMun) || empty($dest->UF)) {
            throw new Exception("Destinatário: Endereço incompleto (Logradouro, Número, Bairro, Município, UF).");
        }

        // 3. Produtos
        if (empty($nf->produtos) || count($nf->produtos) == 0) throw new Exception("Nota Fiscal sem produtos.");

        foreach ($nf->produtos as $index => $prod) {
            $item = $index + 1;
            $nomeProd = $prod['xProd'] ?? "Item $item";

            if (empty($prod['NCM'])) throw new Exception("Produto '$nomeProd': NCM obrigatório.");

            // Valida se NCM tem 8 dígitos
            $ncm = preg_replace('/\D/', '', $prod['NCM']);
            if (strlen($ncm) !== 8) throw new Exception("Produto '$nomeProd': NCM inválido ({$prod['NCM']}). Deve ter 8 dígitos.");

            if (empty($prod['CFOP'])) throw new Exception("Produto '$nomeProd': CFOP obrigatório.");

            // Garante unidade comercial mesmo para itens antigos ou serviços sem unidade definida
            $uCom = isset($prod['uCom']) ? trim((string)$prod['uCom']) : '';
            if ($uCom === '' && isset($prod['unidade_comercial'])) {
                $uCom = trim((string)$prod['unidade_comercial']);
            }
            if ($uCom === '') {
                $uCom = 'UN';
            }

            if (!isset($prod['vProd']) || $prod['vProd'] <= 0) throw new Exception("Produto '$nomeProd': Valor total deve ser maior que zero.");
        }
    }

    /**
     * Transmite uma NF-e pendente
     */
    public function transmitirNFe(NotaFiscal $notaFiscal)
    {
        try {
            // Valida requisitos antes de tentar qualquer coisa
            $this->validarRequisitosEmissao($notaFiscal);

            if ($notaFiscal->status == 'autorizada') {
                throw new Exception('Esta NF-e já está autorizada.');
            }

            if (!$this->tools) {
                $this->loadCertificate();
            }

            // Garante número e série antes de montar XML
            $serie = Configuracao::get('nfe_serie') ?: 1;
            $nfeNumero = $notaFiscal->numero_nfe ?: $this->getNextNfeNumber();

            $notaFiscal->update([
                'numero_nfe' => $nfeNumero,
                'serie' => $serie,
            ]);

            // Monta o XML
            $xml = $this->montarXml($notaFiscal);

            // Assina
            $xmlAssinado = $this->tools->signNFe($xml);

            $idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
            $indSinc = 1;
            $resp = $this->tools->sefazEnviaLote([$xmlAssinado], $idLote, $indSinc);

            $st = new Standardize();
            $std = $st->toStd($resp);

            if ($indSinc === 1) {
                if ($std->cStat != 104) {
                    throw new Exception("Erro ao enviar lote: [{$std->cStat}] {$std->xMotivo}");
                }
                $stdProt = $std;
                $protocoloXml = $resp;
            } else {
                if ($std->cStat != 103) {
                    throw new Exception("Erro ao enviar lote: [{$std->cStat}] {$std->xMotivo}");
                }

                $recibo = $std->infRec->nRec;
                sleep(2);
                $protocoloXml = $this->tools->sefazConsultaRecibo($recibo);
                $stdProt = $st->toStd($protocoloXml);

                if ($stdProt->cStat != 104) {
                    throw new Exception("Erro ao consultar recibo: [{$stdProt->cStat}] {$stdProt->xMotivo}");
                }
            }

            $protNFe = $stdProt->protNFe;
            if ($protNFe->infProt->cStat != 100) {
                throw new Exception("Rejeição: [{$protNFe->infProt->cStat}] {$protNFe->infProt->xMotivo}");
            }

            $xmlProtocolado = \NFePHP\NFe\Complements::toAuthorize($xmlAssinado, $protocoloXml);

            $notaFiscal->update([
                'status' => 'autorizada',
                'chave_acesso' => $protNFe->infProt->chNFe,
                'protocolo' => $protNFe->infProt->nProt,
                'xml' => $xmlProtocolado,
                'data_emissao' => date('Y-m-d H:i:s'),
                'motivo_rejeicao' => null,
            ]);

            // Atualiza o último número utilizado nas configurações para garantir sequencial correto
            // Isso previne erro de duplicidade caso o banco local esteja desincronizado
            try {
                Configuracao::set('nfe_ultimo_numero', $notaFiscal->numero_nfe, 'nfe', 'number');
            } catch (\Exception $e) {
                Log::warning('Falha ao atualizar nfe_ultimo_numero após autorização: ' . $e->getMessage());
            }

            return $notaFiscal;
        } catch (Exception $e) {
            // Log erro
            Log::error('Erro ao transmitir NF-e: ' . $e->getMessage());
            $notaFiscal->update(['status' => 'rejeitada', 'motivo_rejeicao' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Cancela uma NF-e autorizada
     */
    public function cancelarNFe(NotaFiscal $notaFiscal, $justificativa)
    {
        try {
            if (!$this->tools) {
                $this->loadCertificate();
            }

            if ($notaFiscal->status != 'autorizada') {
                throw new Exception("Apenas NF-e autorizada pode ser cancelada.");
            }

            if (empty($notaFiscal->protocolo)) {
                if (!empty($notaFiscal->chave_acesso)) {
                    try {
                        $this->consultarStatus($notaFiscal);
                        $notaFiscal->refresh();
                    } catch (Exception $e) {
                        Log::warning("Falha ao consultar status da NF-e {$notaFiscal->id} antes do cancelamento: " . $e->getMessage());
                    }
                }
            }

            if ($notaFiscal->status != 'autorizada') {
                throw new Exception("Apenas NF-e autorizada pode ser cancelada. Status atual: {$notaFiscal->status}.");
            }

            if (empty($notaFiscal->protocolo)) {
                throw new Exception("Número do protocolo de autorização não encontrado na SEFAZ.");
            }

            $chave = $notaFiscal->chave_acesso;
            $nProt = $notaFiscal->protocolo;

            // Valida justificativa (min 15 chars)
            if (strlen($justificativa) < 15) {
                throw new Exception("A justificativa deve ter no mínimo 15 caracteres.");
            }

            // Envia evento de cancelamento
            $resp = $this->tools->sefazCancela($chave, $justificativa, $nProt);

            $st = new Standardize();
            $std = $st->toStd($resp);

            // Verifica o status do evento (135 = Evento registrado e vinculado a NF-e)
            // cStat pode ser 135 (Evento registrado e vinculado) ou 155 (Cancelamento homologado fora de prazo)
            if (isset($std->retEvento->infEvento->cStat) && in_array($std->retEvento->infEvento->cStat, ['135', '155'])) {
                // Sucesso
                $xmlCancelamento = $resp; // O XML de retorno contém o evento processado

                $notaFiscal->update([
                    'status' => 'cancelada',
                    'xml_cancelamento' => $xmlCancelamento
                ]);

                return true;
            } else {
                // Erro
                $motivo = $std->retEvento->infEvento->xMotivo ?? 'Erro desconhecido ao cancelar';
                throw new Exception("Erro ao cancelar: " . $motivo);
            }
        } catch (Exception $e) {
            Log::error("Erro ao cancelar NF-e {$notaFiscal->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function inutilizar($serie, $numeroInicial, $numeroFinal, $justificativa)
    {
        try {
            if (!$this->tools) {
                $this->loadCertificate();
            }

            if (strlen($justificativa) < 15) {
                throw new Exception('A justificativa deve ter no mínimo 15 caracteres.');
            }

            $cnpj = preg_replace('/[^0-9]/', '', Configuracao::get('nfe_cnpj') ?: Configuracao::get('empresa_cnpj') ?: '');
            if (empty($cnpj)) {
                throw new Exception('CNPJ do emitente não configurado para inutilização.');
            }

            $ano = date('y');
            $modelo = '55';

            $resp = $this->tools->sefazInutiliza(
                $cnpj,
                (int) $serie,
                (int) $numeroInicial,
                (int) $numeroFinal,
                $ano,
                $justificativa,
                $modelo
            );

            $st = new Standardize();
            $std = $st->toStd($resp);

            if (isset($std->infInut->cStat) && $std->infInut->cStat === '102') {
                Log::info("Inutilização de NF-e homologada: Série {$serie}, {$numeroInicial} a {$numeroFinal}.");
                return true;
            }

            $codigo = $std->infInut->cStat ?? '000';
            $motivo = $std->infInut->xMotivo ?? 'Erro desconhecido ao inutilizar';
            throw new Exception("Erro ao inutilizar numeração: [{$codigo}] {$motivo}");
        } catch (Exception $e) {
            Log::error('Erro ao inutilizar numeração de NF-e: ' . $e->getMessage());
            throw $e;
        }
    }


    public function consultarNotasDestinadas($ultNSU = 0)
    {
        // Verifica se há bloqueio de consulta (Consumo Indevido)
        $nextQuery = Configuracao::get('nfe_next_dfe_query');
        if ($nextQuery) {
            $nextQueryTime = \Carbon\Carbon::parse($nextQuery);
            if (now()->lt($nextQueryTime)) {
                $diff = (int) ceil(now()->diffInMinutes($nextQueryTime));
                throw new Exception("Consulta temporariamente bloqueada pela SEFAZ (Consumo Indevido). Tente novamente em {$diff} minutos.");
            }
        }

        try {
            if (!$this->tools) {
                $this->loadCertificate();
            }

            // Consulta DistribuicaoDFe por NSU
            $resp = $this->tools->sefazDistDFe($ultNSU);

            $std = $this->parseDistDFeResponse($resp);

            // Verifica status de bloqueio ou sem documentos
            // 137: Nenhum documento localizado -> Deve aguardar 1 hora
            // 656: Consumo Indevido -> Deve aguardar 1 hora
            if ($std->cStat == 656) {
                Configuracao::set('nfe_next_dfe_query', now()->addHour()->toDateTimeString(), 'nfe', 'datetime', 'Próxima consulta DFe permitida');
            } elseif ($std->cStat == 137) {
                Configuracao::set('nfe_next_dfe_query', now()->addMinutes(5)->toDateTimeString(), 'nfe', 'datetime', 'Próxima consulta DFe permitida');
            }

            if ($std->cStat != 138 && $std->cStat != 137) { // 138: Documentos localizados, 137: Nenhum documento
                throw new Exception("Erro na consulta DFe: [{$std->cStat}] {$std->xMotivo}");
            }

            return $std;
        } catch (Exception $e) {
            Log::error('Erro ao consultar notas destinadas: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Baixa o XML de uma nota pela chave de acesso
     */
    public function baixarPorChave($chave)
    {
        Log::info('NFeService: Iniciando baixarPorChave', ['chave' => $chave]);

        // Verifica se há bloqueio de consulta (Consumo Indevido)
        $nextQuery = Configuracao::get('nfe_next_dfe_query');
        if ($nextQuery) {
            $nextQueryTime = \Carbon\Carbon::parse($nextQuery);
            if (now()->lt($nextQueryTime)) {
                $diff = (int) ceil(now()->diffInMinutes($nextQueryTime));
                throw new Exception("Consulta temporariamente bloqueada pela SEFAZ (Consumo Indevido). O sistema deve aguardar {$diff} minutos antes de tentar novamente.");
            }
        }

        try {
            // Garante que a chave contenha apenas números
            $chave = preg_replace('/[^0-9]/', '', $chave);
            Log::info('NFeService: Chave sanitizada para busca', ['chave' => $chave]);

            if (!$this->tools) {
                $this->loadCertificate();
            }

            // Consulta DistribuicaoDFe por Chave (último parâmetro)
            // sefazDistDFe($ultNSU = 0, $nsu = 0, $chave = '', $cnpj = '')
            // Para buscar por chave, NSU deve ser 0
            $resp = $this->tools->sefazDistDFe(0, 0, $chave);

            $std = $this->parseDistDFeResponse($resp);

            // Verifica status de bloqueio ou sem documentos
            // 656: Consumo Indevido -> Deve aguardar 1 hora
            if ($std->cStat == 656) {
                Configuracao::set('nfe_next_dfe_query', now()->addHour()->toDateTimeString(), 'nfe', 'datetime', 'Próxima consulta DFe permitida');
                throw new Exception("Rejeição: Consumo Indevido (656). A SEFAZ bloqueou consultas por 1 hora devido ao excesso de tentativas. Tente novamente mais tarde.");
            }

            // Se erro for "Rejeição: NF-e inexistente" (217), pode ser necessário manifestar primeiro
            // Mas se for inexistente MESMO, manifestar não ajuda.
            // Porém, muitas vezes o erro de "não autorizado a baixar" vem mascarado ou o download só libera após Ciência.
            // Vamos tentar fazer Ciência da Operação se não encontrar de primeira, e tentar baixar de novo.

            if ($std->cStat != 138) {
                // Se erro for diferente de 138 (Documentos localizados)

                // Tenta manifestar Ciência da Operação (210210) automaticamente
                try {
                    Log::info('Tentando manifestar Ciência da Operação automaticamente para: ' . $chave);
                    $this->manifestar($chave, 210210);

                    // Aguarda um pouco para propagação (opcional, mas recomendado)
                    sleep(2);

                    // Tenta baixar novamente
                    $resp = $this->tools->sefazDistDFe(0, 0, $chave);
                    $std = $this->parseDistDFeResponse($resp);
                } catch (\Exception $eManifest) {
                    Log::warning('Falha ao manifestar automaticamente: ' . $eManifest->getMessage());

                    // Se falhar por prazo (Erro 596), tenta manifestar CONFIRMAÇÃO DA OPERAÇÃO (210200)
                    // A Ciência (210210) tem prazo de 10 dias, mas a Confirmação (210200) tem prazo maior (até 180 dias)
                    if (str_contains($eManifest->getMessage(), '596')) {
                        try {
                            Log::info('Prazo de Ciência expirado (10 dias). Tentando manifestar Confirmação da Operação (210200) para baixar XML: ' . $chave);
                            // 210200 = Confirmação da Operação
                            $this->manifestar($chave, 210200);

                            // Aguarda propagação (aumentado para 5s)
                            sleep(5);

                            // Tenta baixar novamente após confirmação (com retentativas)
                            $maxRetries = 5;
                            $retryCount = 0;
                            $success = false;

                            do {
                                // Verifica bloqueio antes de cada tentativa no loop
                                $nextQueryLoop = Configuracao::get('nfe_next_dfe_query');
                                if ($nextQueryLoop && now()->lt(\Carbon\Carbon::parse($nextQueryLoop))) {
                                    break;
                                }

                                $resp = $this->tools->sefazDistDFe(0, 0, $chave);
                                $std = $this->parseDistDFeResponse($resp);

                                if ($std->cStat == 656) {
                                    Configuracao::set('nfe_next_dfe_query', now()->addHour()->toDateTimeString(), 'nfe', 'datetime', 'Próxima consulta DFe permitida');
                                    throw new Exception("Rejeição: Consumo Indevido (656) durante tentativas. Bloqueio de 1h ativado.");
                                }

                                if ($std->cStat == 138) {
                                    $success = true;
                                    break;
                                }

                                // Se ainda não disponível (137), aguarda mais um pouco
                                if ($std->cStat == 137) {
                                    Log::info("Tentativa " . ($retryCount + 1) . " de download pós-confirmação: Documento ainda não localizado (137). Aguardando...");
                                    sleep(7);
                                    $retryCount++;
                                } else {
                                    // Outro erro, aborta
                                    break;
                                }
                            } while ($retryCount < $maxRetries);

                            if (!$success && $std->cStat == 137) {
                                throw new Exception("A 'Confirmação da Operação' foi realizada com sucesso, mas a SEFAZ ainda não liberou o XML para download. Isso é comum e pode levar alguns minutos. Por favor, aguarde e tente baixar novamente mais tarde.");
                            }

                            // Se chegou aqui com sucesso, o fluxo continua e vai retornar o XML abaixo
                        } catch (\Exception $eConfirm) {
                            Log::warning('Falha ao manifestar Confirmação (fallback): ' . $eConfirm->getMessage());

                            // Se falhar também a confirmação, tenta API pública como último recurso
                            Log::info('Tentando baixar via API pública (fallback final): ' . $chave);
                            try {
                                $publicXml = $this->baixarViaApiPublica($chave);
                                if ($publicXml) {
                                    return [
                                        'schema' => 'procNFe_v4.00.xsd', // Assume procNFe
                                        'content' => $publicXml,
                                        'nsu' => '000000000000000' // NSU fictício
                                    ];
                                }
                            } catch (\Exception $ePublic) {
                                Log::warning('Falha no fallback de API pública: ' . $ePublic->getMessage());
                            }
                        }
                    }
                }

                if ($std->cStat != 138) {
                    // Tenta fallback final também aqui se não tentou antes (caso o erro não seja de manifestação, mas de download direto falho)
                    if ($std->cStat == 137) { // Nenhum documento localizado
                        try {
                            $publicXml = $this->baixarViaApiPublica($chave);
                            if ($publicXml) {
                                return [
                                    'schema' => 'procNFe_v4.00.xsd',
                                    'content' => $publicXml,
                                    'nsu' => '000000000000000'
                                ];
                            }
                        } catch (\Exception $ePublic) {
                            Log::warning('Falha no fallback de API pública (tentativa 2): ' . $ePublic->getMessage());
                        }
                    }

                    throw new Exception("Erro ao baixar NFe por chave: [{$std->cStat}] {$std->xMotivo}");
                }
            }

            if (!isset($std->loteDistDFeInt->docZip)) {
                throw new Exception("Nenhum documento retornado para a chave informada.");
            }

            $doc = $std->loteDistDFeInt->docZip;

            // O retorno pode ser um array se houver duplicidade (raro por chave) ou objeto
            if (is_array($doc)) {
                $doc = $doc[0];
            }

            $schema = $doc->schema;
            $contentEncoded = $doc->{'$'} ?? $doc->{0} ?? null;

            if (!$contentEncoded) {
                // Fallback busca string
                foreach ($doc as $key => $value) {
                    if (is_string($value) && strlen($value) > 20) {
                        $contentEncoded = $value;
                        break;
                    }
                }
            }

            if (!$contentEncoded) {
                throw new Exception("Conteúdo do XML não encontrado na resposta.");
            }

            return [
                'schema' => $schema,
                'content' => gzdecode(base64_decode($contentEncoded)),
                'nsu' => $doc->NSU
            ];
        } catch (Exception $e) {
            Log::error('Erro ao baixar NFe por chave: ' . $e->getMessage());

            // Tenta fallback via API pública
            $xmlFallback = $this->baixarViaApiPublica($chave);
            if ($xmlFallback) {
                return [
                    'schema' => 'procNFe_v4.00.xsd', // Assume procNFe
                    'content' => $xmlFallback,
                    'nsu' => '000000000000000' // NSU fictício
                ];
            }

            throw $e;
        }
    }

    /**
     * Tenta baixar o XML via API pública (Fallback)
     * Implementação inspirada em serviços como fsist.com.br ou nfe.io
     */
    public function baixarViaApiPublica($chave)
    {
        // Verifica se existe URL configurada para API pública
        $apiUrl = Configuracao::get('nfe_api_publica_url');
        $apiToken = Configuracao::get('nfe_api_publica_token');

        if (!$apiUrl) {
            // Se não tiver API configurada, tenta um serviço gratuito conhecido ou retorna null
            Log::info('Nenhuma API pública configurada para download de XML.');
            return null;
        }

        try {
            Log::info("Tentando baixar XML via API pública: $apiUrl");

            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $apiUrl . '/' . $chave, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Accept' => 'application/json',
                ],
                'timeout' => 15
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['xml'])) {
                $xml = base64_decode($data['xml']);
                // Valida se é um XML válido
                if (str_contains($xml, '<nfeProc') || str_contains($xml, '<NFe')) {
                    return $xml;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao baixar via API pública: ' . $e->getMessage());
            return null;
        }
    }

    public function manifestar($chave, $evento = 210210, $justificativa = '')
    {
        try {
            if (!$this->tools) {
                $this->loadCertificate();
            }

            // 210200 = Confirmação da Operação
            // 210210 = Ciência da Operação
            // 210220 = Desconhecimento da Operação
            // 210240 = Operação não Realizada

            $tpEvento = $evento;
            $xJust = $justificativa;
            $nSeqEvento = 1;

            $resp = $this->tools->sefazManifesta($chave, $tpEvento, $xJust, $nSeqEvento);

            $st = new Standardize();
            $std = $st->toStd($resp);

            if ($std->cStat != 128) {
                // 128 = Lote de Evento Processado
                throw new Exception("Erro ao enviar evento: [{$std->cStat}] {$std->xMotivo}");
            }

            $retEvento = $std->retEvento;
            if ($retEvento->infEvento->cStat != 135 && $retEvento->infEvento->cStat != 136 && $retEvento->infEvento->cStat != 573) {
                // 135 = Evento registrado e vinculado a NF-e
                // 136 = Evento registrado, mas não vinculado a NF-e
                // 573 = Duplicidade de Evento
                throw new Exception("Erro ao manifestar: [{$retEvento->infEvento->cStat}] {$retEvento->infEvento->xMotivo}");
            }

            return $std;
        } catch (Exception $e) {
            Log::error('Erro ao manifestar nota: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDadosEmitente()
    {
        $codigoMunicipio = Configuracao::get('nfe_endereco_codigo_municipio') ?: '';
        $codigoMunicipio = preg_replace('/\D/', '', (string) $codigoMunicipio);
        if (strlen($codigoMunicipio) !== 7) {
            $codigoMunicipio = '3304557';
        }

        $foneConfig = Configuracao::get('nfe_telefone') ?: '';
        $foneDigits = preg_replace('/\D/', '', (string) $foneConfig);
        if ($foneDigits !== '' && (strlen($foneDigits) < 6 || strlen($foneDigits) > 14)) {
            $foneDigits = '';
        }

        return (object) [
            'xNome' => $this->config['razaosocial'],
            'xFant' => Configuracao::get('nfe_nome_fantasia') ?: Configuracao::get('empresa_nome') ?: '',
            'IE' => Configuracao::get('nfe_ie') ?: '',
            'CRT' => Configuracao::get('nfe_crt') ?: 3,
            'CNPJ' => $this->config['cnpj'],
            'xLgr' => Configuracao::get('nfe_endereco_logradouro') ?: '',
            'nro' => Configuracao::get('nfe_endereco_numero') ?: '',
            'xCpl' => Configuracao::get('nfe_endereco_complemento') ?: '',
            'xBairro' => Configuracao::get('nfe_endereco_bairro') ?: '',
            'cMun' => $codigoMunicipio,
            'xMun' => Configuracao::get('nfe_endereco_municipio') ?: '',
            'UF' => $this->config['siglaUF'],
            'CEP' => Configuracao::get('nfe_cep') ?: '',
            'cPais' => '1058',
            'xPais' => 'BRASIL',
            'fone' => $foneDigits,
        ];
    }

    protected function getDadosDestinatario($cliente)
    {
        $end = $cliente->endereco;
        if (!$end) return null;

        return [
            'xNome' => $cliente->nome,
            'cpf_cnpj' => $cliente->cpf_cnpj,
            'IE' => $cliente->inscricao_estadual,
            'email' => $cliente->email,
            'xLgr' => $end->logradouro,
            'nro' => $end->numero,
            'xCpl' => $end->complemento,
            'xBairro' => $end->bairro,
            'cMun' => $end->codigo_municipio ?? '3304557',
            'xMun' => $end->cidade,
            'UF' => $end->uf,
            'CEP' => $end->cep,
        ];
    }

    protected function getDadosProduto($produto, $venda, $itemIndex)
    {
        return [
            'item' => $itemIndex,
            'cProd' => $produto->id,
            'cEAN' => $produto->codigo_barras ?: 'SEM GTIN',
            'xProd' => $produto->nome,
            'NCM' => $produto->ncm ?: '00000000',
            'CFOP' => $produto->cfop_externo ?: '5102',
            'uCom' => $produto->unidade_comercial ?: 'UN',
            'qCom' => $produto->pivot->quantidade ?? 1,
            'vUnCom' => $produto->pivot->preco_unitario ?? $produto->preco_venda,
            'vProd' => ($produto->pivot->quantidade ?? 1) * ($produto->pivot->preco_unitario ?? $produto->preco_venda),
            'cEANTrib' => 'SEM GTIN',
            'uTrib' => $produto->unidade_tributavel ?: 'UN',
            'qTrib' => $produto->pivot->quantidade ?? 1,
            'vUnTrib' => $produto->pivot->preco_unitario ?? $produto->preco_venda,
            'indTot' => 1,
            'orig' => $produto->origem ?: 0,
            'CSOSN' => $produto->csosn_icms ?: '102',
            'cst_pis' => $produto->cst_pis,
            'cst_cofins' => $produto->cst_cofins,
        ];
    }

    protected function getCodigoUF($sigla)
    {
        $ufs = ['RO' => '11', 'AC' => '12', 'AM' => '13', 'RR' => '14', 'PA' => '15', 'AP' => '16', 'TO' => '17', 'MA' => '21', 'PI' => '22', 'CE' => '23', 'RN' => '24', 'PB' => '25', 'PE' => '26', 'AL' => '27', 'SE' => '28', 'BA' => '29', 'MG' => '31', 'ES' => '32', 'RJ' => '33', 'SP' => '35', 'PR' => '41', 'SC' => '42', 'RS' => '43', 'MS' => '50', 'MT' => '51', 'GO' => '52', 'DF' => '53'];
        return $ufs[$sigla] ?? '33';
    }

    protected function getNextNfeNumber()
    {
        $lastDb = NotaFiscal::whereNotNull('numero_nfe')->max('numero_nfe');
        $lastConfig = (int) Configuracao::get('nfe_ultimo_numero', 0);

        $last = max($lastDb, $lastConfig);

        return $last ? $last + 1 : 1;
    }

    protected function calcularTotais($produtos)
    {
        $total = 0;
        foreach ($produtos as $p) {
            $total += $p['vProd'];
        }
        return [
            'vBC' => 0,
            'vICMS' => 0,
            'vICMSDeson' => 0,
            'vFCP' => 0,
            'vBCST' => 0,
            'vST' => 0,
            'vFCPST' => 0,
            'vFCPSTRet' => 0,
            'vProd' => $total,
            'vFrete' => 0,
            'vSeg' => 0,
            'vDesc' => 0,
            'vII' => 0,
            'vIPI' => 0,
            'vIPIDevol' => 0,
            'vPIS' => 0,
            'vCOFINS' => 0,
            'vOutro' => 0,
            'vNF' => $total,
        ];
    }

    /**
     * Helper para parsear resposta do DFe corrigindo problemas de atributos perdidos (NSU/schema)
     * em elementos docZip quando convertidos para stdClass via json_encode
     */
    private function parseDistDFeResponse($resp)
    {
        $st = new Standardize();
        $std = $st->toStd($resp);

        // CORREÇÃO: Forçar estrutura correta de docZip preservando atributos (NSU/schema)
        try {
            $xml = simplexml_load_string($resp);
            if (isset($xml->loteDistDFeInt->docZip)) {
                $docZips = [];
                foreach ($xml->loteDistDFeInt->docZip as $node) {
                    $doc = new stdClass();
                    $doc->NSU = (string) $node['NSU'];
                    $doc->schema = (string) $node['schema'];
                    $doc->{'$'} = (string) $node;
                    $docZips[] = $doc;
                }
                if (!isset($std->loteDistDFeInt)) {
                    $std->loteDistDFeInt = new stdClass();
                }
                $std->loteDistDFeInt->docZip = $docZips;
            }
        } catch (\Exception $e) {
            // Silencioso se falhar, mantém original
        }

        return $std;
    }
}
