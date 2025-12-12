<?php

namespace App\Services;

use App\Models\NotaFiscal;
use App\Models\Venda;
use App\Models\Clientes;
use App\Models\Produto;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
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
            'razaosocial' => Configuracao::get('nfe_razao_social') ?: Configuracao::get('empresa_nome') ?: 'JBTECH Informática',
            'siglaUF' => Configuracao::get('empresa_uf') ?: 'RJ',
            'cnpj' => Configuracao::get('nfe_cnpj') ?: Configuracao::get('empresa_cnpj') ?: '',
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tokenIBPT' => Configuracao::get('nfe_token_ibpt') ?: '',
            'CSC' => Configuracao::get('nfe_csc') ?: '',
            'CSCid' => Configuracao::get('nfe_csc_id') ?: '',
        ];

        // Carrega o certificado digital
        $this->loadCertificate();
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
        
        return $opensslConfigPath;
    }

    /**
     * Carrega o certificado digital
     */
    protected function loadCertificate()
    {
        try {
            // SEMPRE usa o banco de dados, não o .env (múltiplas empresas podem usar o sistema)
            $certPathConfig = Configuracao::get('nfe_cert_path');
            $certPath = storage_path('app/certificates/' . ($certPathConfig ?: 'certificado.pfx'));
            
            // Busca a senha diretamente do banco (sem usar get() que pode retornar default)
            $configSenha = Configuracao::where('chave', 'nfe_cert_password')->first();
            $certPassword = $configSenha && $configSenha->valor !== null && $configSenha->valor !== '' ? $configSenha->valor : null;

            if (!file_exists($certPath)) {
                throw new Exception('Certificado digital não encontrado em: ' . $certPath . '. Por favor, faça o upload do certificado nas configurações.');
            }

            if (empty($certPassword)) {
                throw new Exception('Senha do certificado digital não configurada no banco de dados. Por favor, configure a senha nas configurações do sistema (Configurações → NF-e).');
            }

            $certContent = file_get_contents($certPath);
            
            // Configura OpenSSL para usar provider legacy (OpenSSL 3.x) ANTES de qualquer uso
            $this->configurarOpenSSLLegacy();
            
            // Tenta primeiro com openssl_pkcs12_read para OpenSSL 3.x
            if (function_exists('openssl_pkcs12_read')) {
                $certData = null;
                $result = @openssl_pkcs12_read($certContent, $certData, $certPassword);
                if ($result && isset($certData['cert'])) {
                    // Se funcionou com openssl_pkcs12_read, ainda precisa usar NFePHP para manter compatibilidade
                }
            }
            
            // Usa o método do NFePHP
            try {
                $this->certificate = Certificate::readPfx($certContent, $certPassword);
                $this->tools = new Tools(json_encode($this->config), $this->certificate);
            } catch (\Exception $e) {
                // Se falhar, tenta uma abordagem alternativa
                $errorMsg = $e->getMessage();
                if (str_contains($errorMsg, 'digital envelope') || str_contains($errorMsg, 'unsupported')) {
                    // Tenta novamente com configuração mais agressiva
                    $this->configurarOpenSSLLegacy();
                    
                    // Tenta novamente com openssl_pkcs12_read
                    if (function_exists('openssl_pkcs12_read')) {
                        $certData = null;
                        $result = @openssl_pkcs12_read($certContent, $certData, $certPassword);
                        if ($result && isset($certData['cert'])) {
                            // Tenta novamente com NFePHP
                            try {
                                $this->certificate = Certificate::readPfx($certContent, $certPassword);
                                $this->tools = new Tools(json_encode($this->config), $this->certificate);
                            } catch (\Exception $e2) {
                                throw new Exception('Erro ao ler certificado com OpenSSL 3.x mesmo com provider legacy ativado. Detalhes: ' . $e2->getMessage());
                            }
                        } else {
                            throw new Exception('Erro ao ler certificado. Verifique se a senha está correta e se o certificado está no formato PFX válido.');
                        }
                    } else {
                        throw new Exception('Erro de compatibilidade com OpenSSL 3.x. A função openssl_pkcs12_read não está disponível.');
                    }
                } else {
                    throw $e;
                }
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Trata erros específicos do certificado
            if (str_contains($errorMessage, 'mac verify failure') || str_contains($errorMessage, 'PKCS12')) {
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
     * Emite uma NF-e a partir de uma venda
     */
    public function emitirNFe(Venda $venda)
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
            $venda->load(['cliente.endereco', 'produtos']);
            $cliente = $venda->cliente;

            if (!$cliente->endereco) {
                throw new Exception('Cliente não possui endereço cadastrado.');
            }

            // Cria a estrutura da NF-e
            $make = new Make();
            
            // Dados do emitente (empresa)
            $emitente = $this->getDadosEmitente();
            $make->taginfNFe($emitente);

            // Dados do destinatário (cliente)
            $destinatario = $this->getDadosDestinatario($cliente);
            $make->tagdest($destinatario);

            // Adiciona os produtos
            foreach ($venda->produtos as $produto) {
                $produtoNFe = $this->getDadosProduto($produto, $venda);
                $make->tagprod($produtoNFe);
            }

            // Gera o XML
            $xml = $make->getXML();

            // Assina o XML
            $xmlAssinado = $this->tools->signNFe($xml);

            // Envia para SEFAZ
            $response = $this->tools->sefazEnviaLote([$xmlAssinado], 1);

            // Processa a resposta
            $std = new Standardize($response);
            $stdCl = $std->toStd();

            // Cria o registro da NF-e
            $notaFiscal = NotaFiscal::create([
                'venda_id' => $venda->id,
                'cliente_id' => $venda->cliente_id,
                'numero_nfe' => null, // Será preenchido após autorização
                'chave_acesso' => null, // Será preenchido após autorização
                'status' => 'processando',
                'xml' => $xmlAssinado,
                'valor_total' => $venda->valor_total,
                'data_emissao' => now(),
                'dados_emitente' => $emitente,
                'dados_destinatario' => $destinatario,
                'produtos' => $venda->produtos->map(function ($produto) use ($venda) {
                    return [
                        'id' => $produto->id,
                        'nome' => $produto->nome,
                        'ncm' => $produto->ncm,
                        'quantidade' => $produto->pivot->quantidade,
                        'valor_unitario' => $produto->pivot->valor_unitario,
                        'valor_total' => $produto->pivot->valor_total,
                    ];
                })->toArray(),
            ]);

            // Consulta o recibo para obter a chave de acesso
            if (isset($stdCl->infRec->nRec)) {
                $this->consultarRecibo($notaFiscal, $stdCl->infRec->nRec);
            }

            return $notaFiscal;
        } catch (Exception $e) {
            Log::error('Erro ao emitir NF-e: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Consulta o recibo da NF-e
     */
    protected function consultarRecibo(NotaFiscal $notaFiscal, $numeroRecibo)
    {
        try {
            $response = $this->tools->sefazConsultaRecibo($numeroRecibo);
            $std = new Standardize($response);
            $stdCl = $std->toStd();

            if (isset($stdCl->protNFe->infProt)) {
                $infProt = $stdCl->protNFe->infProt;
                
                if ($infProt->cStat == '100') { // Autorizada
                    $notaFiscal->update([
                        'status' => 'autorizada',
                        'numero_nfe' => $infProt->nProt,
                        'chave_acesso' => $infProt->chNFe,
                        'protocolo' => $infProt->nProt,
                        'data_emissao' => now(),
                    ]);
                } else {
                    $notaFiscal->update([
                        'status' => 'rejeitada',
                        'motivo_rejeicao' => $infProt->xMotivo,
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('Erro ao consultar recibo: ' . $e->getMessage());
            $notaFiscal->update([
                'status' => 'rejeitada',
                'motivo_rejeicao' => 'Erro ao consultar recibo: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtém os dados do emitente
     */
    protected function getDadosEmitente()
    {
        // Remove formatação dos campos numéricos
        $cnpj = preg_replace('/[^0-9]/', '', Configuracao::get('nfe_cnpj') ?: Configuracao::get('empresa_cnpj') ?: '');
        $cep = preg_replace('/[^0-9]/', '', Configuracao::get('empresa_cep') ?: '');
        $telefone = preg_replace('/[^0-9]/', '', Configuracao::get('empresa_telefone') ?: '');
        
        return [
            'xNome' => Configuracao::get('nfe_razao_social') ?: Configuracao::get('empresa_nome') ?: 'JBTECH Informática',
            'xFant' => Configuracao::get('nfe_nome_fantasia') ?: '',
            'IE' => Configuracao::get('nfe_ie') ?: '',
            'IEST' => Configuracao::get('nfe_iest') ?: '',
            'CRT' => Configuracao::get('nfe_crt') ?: '3',
            'CNPJ' => $cnpj,
            'CPF' => Configuracao::get('nfe_cpf') ?: '',
            'xLgr' => Configuracao::get('empresa_endereco') ?: '',
            'nro' => Configuracao::get('empresa_numero') ?: '',
            'xCpl' => Configuracao::get('empresa_complemento') ?: '',
            'xBairro' => Configuracao::get('empresa_bairro') ?: '',
            'cMun' => Configuracao::get('empresa_codigo_municipio') ?: '',
            'xMun' => Configuracao::get('empresa_cidade') ?: '',
            'UF' => Configuracao::get('empresa_uf') ?: 'RJ',
            'CEP' => $cep,
            'cPais' => '1058',
            'xPais' => 'BRASIL',
            'fone' => $telefone,
            'email' => Configuracao::get('empresa_email') ?: '',
        ];
    }

    /**
     * Obtém os dados do destinatário
     */
    protected function getDadosDestinatario(Clientes $cliente)
    {
        $endereco = $cliente->endereco;
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cliente->cpf_cnpj);
        
        $dados = [
            'xNome' => $cliente->nome,
            'email' => $cliente->email ?? '',
        ];

        // Define se é CPF ou CNPJ
        if (strlen($cpfCnpj) == 11) {
            $dados['CPF'] = $cpfCnpj;
        } elseif (strlen($cpfCnpj) == 14) {
            $dados['CNPJ'] = $cpfCnpj;
        } else {
            throw new Exception('CPF/CNPJ do cliente inválido.');
        }

        // Dados do endereço
        if ($endereco) {
            $dados['xLgr'] = $endereco->endereco;
            $dados['nro'] = $endereco->numero ?? 'S/N';
            $dados['xBairro'] = $endereco->bairro;
            $dados['cMun'] = $this->getCodigoMunicipio($endereco->cidade, $endereco->estado);
            $dados['xMun'] = $endereco->cidade;
            $dados['UF'] = $endereco->estado;
            $dados['CEP'] = preg_replace('/[^0-9]/', '', $endereco->cep ?? '');
            $dados['cPais'] = '1058';
            $dados['xPais'] = 'BRASIL';
            $dados['fone'] = preg_replace('/[^0-9]/', '', $cliente->telefone ?? '');
        }

        return $dados;
    }

    /**
     * Obtém os dados do produto para NF-e
     */
    protected function getDadosProduto(Produto $produto, Venda $venda)
    {
        $pivot = $produto->pivot;
        
        return [
            'cProd' => (string) $produto->id,
            'cEAN' => $produto->codigo_barras ?? '',
            'xProd' => $produto->nome,
            'NCM' => $produto->ncm ?? '00000000',
            'CFOP' => '5102', // Venda de mercadoria adquirida ou recebida de terceiros
            'uCom' => 'UN', // Unidade
            'qCom' => (float) $pivot->quantidade,
            'vUnCom' => (float) $pivot->valor_unitario,
            'vProd' => (float) $pivot->valor_total,
            'cEANTrib' => $produto->codigo_barras ?? '',
            'uTrib' => 'UN',
            'qTrib' => (float) $pivot->quantidade,
            'vUnTrib' => (float) $pivot->valor_unitario,
            'indTot' => '1', // Valor total do item compõe o valor total da NF-e
            'vFrete' => 0,
            'vSeg' => 0,
            'vDesc' => 0,
            'vOutro' => 0,
        ];
    }

    /**
     * Obtém o código do município (simplificado - deve usar tabela IBGE)
     */
    protected function getCodigoMunicipio($cidade, $uf)
    {
        // Esta é uma função simplificada
        // Em produção, deve usar a tabela de municípios do IBGE
        $municipios = [
            'RJ' => [
                'Resende' => '3304508',
                'Rio de Janeiro' => '3304557',
            ],
        ];

        return $municipios[$uf][$cidade] ?? '3304508'; // Default para Resende
    }

    /**
     * Cancela uma NF-e
     */
    public function cancelarNFe(NotaFiscal $notaFiscal, $justificativa)
    {
        try {
            if (!$notaFiscal->podeCancelar()) {
                throw new Exception('NF-e não pode ser cancelada. Status atual: ' . $notaFiscal->status);
            }

            $response = $this->tools->sefazCancela(
                $notaFiscal->chave_acesso,
                $justificativa,
                $notaFiscal->numero_nfe
            );

            $std = new Standardize($response);
            $stdCl = $std->toStd();

            if (isset($stdCl->retEvento->infEvento->cStat) && $stdCl->retEvento->infEvento->cStat == '135') {
                $notaFiscal->update([
                    'status' => 'cancelada',
                    'xml_cancelamento' => $response,
                ]);

                return true;
            } else {
                throw new Exception('Erro ao cancelar NF-e: ' . ($stdCl->retEvento->infEvento->xMotivo ?? 'Erro desconhecido'));
            }
        } catch (Exception $e) {
            Log::error('Erro ao cancelar NF-e: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Consulta o status de uma NF-e
     */
    public function consultarStatus(NotaFiscal $notaFiscal)
    {
        try {
            if (!$notaFiscal->chave_acesso) {
                throw new Exception('NF-e não possui chave de acesso.');
            }

            $response = $this->tools->sefazConsultaChave($notaFiscal->chave_acesso);
            $std = new Standardize($response);
            $stdCl = $std->toStd();

            if (isset($stdCl->protNFe->infProt)) {
                $infProt = $stdCl->protNFe->infProt;
                
                $notaFiscal->update([
                    'status' => $infProt->cStat == '100' ? 'autorizada' : 'rejeitada',
                    'protocolo' => $infProt->nProt,
                    'motivo_rejeicao' => $infProt->cStat != '100' ? $infProt->xMotivo : null,
                ]);

                return $notaFiscal;
            }

            return $notaFiscal;
        } catch (Exception $e) {
            Log::error('Erro ao consultar status da NF-e: ' . $e->getMessage());
            throw $e;
        }
    }
}
