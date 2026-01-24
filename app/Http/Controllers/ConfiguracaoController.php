<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use NFePHP\Common\Certificate;
use Exception;

use App\Services\NFeService;

class ConfiguracaoController extends Controller
{
    /**
     * Exibe a página de configurações
     */
    public function index()
    {
        // Carrega configurações padrão se não existirem
        $this->inicializarConfiguracoes();

        $configuracoes = Configuracao::all()->groupBy('grupo');

        LogService::registrar(
            'Configuração',
            'Visualizar',
            'Acessou a página de configurações'
        );

        return view('content.configuracoes.index', compact('configuracoes'));
    }

    /**
     * Salva as configurações
     */
    public function store(Request $request)
    {
        try {
            $grupo = $request->input('grupo', 'geral');

            // Verificação de permissão para grupos sensíveis
            $gruposSensiveis = ['nfe', 'email', 'sistema'];
            if (in_array($grupo, $gruposSensiveis) && !auth()->user()->isAdmin()) {
                return back()->withErrors('Você não tem permissão para alterar configurações deste grupo.');
            }

            $dados = $request->except(['_token', 'grupo']);

            // Debug: verifica se a senha está chegando
            Log::info('Salvando configurações', [
                'grupo' => $grupo,
                'dados_keys' => array_keys($dados)
            ]);

            // Tratamento especial para upload de logo da interface
            if ($request->hasFile('interface_logo')) {
                $arquivo = $request->file('interface_logo');

                // Valida o tipo de arquivo
                if (!in_array($arquivo->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                    return back()->withErrors('O arquivo de logo deve ser uma imagem (JPG, PNG, GIF ou SVG).');
                }

                $nomeArquivo = 'logo_empresa.' . $arquivo->getClientOriginalExtension();
                $caminho = 'logos/' . $nomeArquivo;

                // Cria o diretório se não existir
                if (!Storage::exists('logos')) {
                    Storage::makeDirectory('logos');
                }

                // Salva o arquivo
                Storage::putFileAs('logos', $arquivo, $nomeArquivo);

                Configuracao::set('interface_logo', $caminho, 'interface', 'file', 'Logo da empresa');
            }

            foreach ($dados as $chave => $valor) {
                // Ignora o campo de upload de arquivo (já foi processado ou é tratado separadamente)
                if ($chave === 'nfe_certificado' || $chave === 'interface_logo') {
                    continue;
                }

                // Ignora campos vazios de senha (para não sobrescrever)
                if ((str_contains($chave, 'senha') || str_contains($chave, 'password')) && empty($valor)) {
                    continue;
                }

                // Determina o grupo e tipo baseado na chave
                $configGrupo = $grupo;
                $configTipo = 'text';

                if (str_starts_with($chave, 'email_')) {
                    $configGrupo = 'email';
                } elseif (str_starts_with($chave, 'sistema_')) {
                    $configGrupo = 'sistema';
                } elseif (str_starts_with($chave, 'produtos_')) {
                    $configGrupo = 'produtos';
                } elseif (str_starts_with($chave, 'vendas_')) {
                    $configGrupo = 'vendas';
                } elseif (str_starts_with($chave, 'clientes_')) {
                    $configGrupo = 'clientes';
                } elseif (str_starts_with($chave, 'relatorios_')) {
                    $configGrupo = 'relatorios';
                } elseif (str_starts_with($chave, 'interface_')) {
                    $configGrupo = 'interface';
                } elseif (str_starts_with($chave, 'empresa_')) {
                    $configGrupo = 'geral';
                }

                // Define o tipo baseado no nome do campo
                if (str_contains($chave, 'senha') || str_contains($chave, 'password')) {
                    $configTipo = 'password';
                } elseif (str_contains($chave, 'certificado') || str_contains($chave, 'arquivo') || str_contains($chave, 'logo')) {
                    $configTipo = 'file';
                } elseif (
                    str_contains($chave, 'ambiente') || str_contains($chave, 'porta') || str_contains($chave, 'paginacao') ||
                    str_contains($chave, 'desconto') || str_contains($chave, 'comissao') || str_contains($chave, 'limite') ||
                    str_contains($chave, 'prazo') || str_contains($chave, 'periodo') || str_contains($chave, 'estoque_minimo') ||
                    str_contains($chave, 'garantia') || str_contains($chave, 'taxa')
                ) {
                    $configTipo = 'number';
                } elseif (
                    str_contains($chave, 'ativo') || str_contains($chave, 'habilitado') || str_contains($chave, 'edicao_inline') ||
                    str_contains($chave, 'exigir') || str_contains($chave, 'permitir') || str_contains($chave, 'gerar') ||
                    str_contains($chave, 'imprimir') || str_contains($chave, 'enviar') || str_contains($chave, 'alertar') ||
                    str_contains($chave, 'mostrar') || str_contains($chave, 'agrupar') || str_contains($chave, 'controle') ||
                    str_contains($chave, 'venda_estoque_negativo')
                ) {
                    $configTipo = 'boolean';
                } elseif (str_contains($chave, 'email')) {
                    $configTipo = 'email';
                }

                // Remove formatação de campos numéricos antes de salvar
                if (
                    str_contains($chave, 'cnpj') || str_contains($chave, 'cpf') ||
                    str_contains($chave, 'cep') || str_contains($chave, 'telefone')
                ) {
                    $valor = preg_replace('/[^0-9]/', '', $valor);
                }

                // Substitui vírgula por ponto em campos decimais (taxas, comissões, descontos)
                if (
                    str_contains($chave, 'taxa') || str_contains($chave, 'comissao') ||
                    str_contains($chave, 'desconto') || str_contains($chave, 'valor')
                ) {
                    $valor = str_replace(',', '.', $valor);
                }

                // Garante que valores boolean sejam salvos como '1' ou '0'
                if ($configTipo === 'boolean') {
                    $valor = ($valor === '1' || $valor === 1 || $valor === true || $valor === 'true' || $valor === 'on') ? '1' : '0';
                }

                // Configurações específicas que são por usuário
                $configuracoesPorUsuario = ['produtos_edicao_inline'];

                // Se for uma configuração por usuário, salva para o usuário autenticado
                // Caso contrário, salva como global (user_id = null)
                $userId = null;
                if (in_array($chave, $configuracoesPorUsuario) && Auth::check()) {
                    $userId = Auth::id();
                }

                Configuracao::set($chave, $valor, $configGrupo, $configTipo, null, $userId);
            }

            LogService::registrar(
                'Configuração',
                'Atualizar',
                "Configurações do grupo {$grupo} atualizadas"
            );

            return redirect()->route('configuracoes.index')
                ->with('success', 'Configurações salvas com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors('Erro ao salvar configurações: ' . $e->getMessage());
        }
    }

    /**
     * Processa especificamente as configurações de NFe
     */
    private function storeNFe(Request $request)
    {
        try {
            Log::info('Iniciando storeNFe');

            // 1. Processa Upload de Certificado (se houver)
            if ($request->hasFile('nfe_certificado')) {
                $arquivo = $request->file('nfe_certificado');

                // Valida extensão
                $extensao = strtolower($arquivo->getClientOriginalExtension());
                if ($extensao !== 'pfx' && $extensao !== 'p12') {
                    return back()->withErrors('O arquivo deve ser um certificado digital no formato PFX ou P12.');
                }

                $nomeArquivo = 'certificado.pfx';
                $caminho = 'certificates/' . $nomeArquivo;

                if (!Storage::exists('certificates')) {
                    Storage::makeDirectory('certificates');
                }

                // Salva temporariamente
                $nomeTemp = 'certificado_temp_' . time() . '.pfx';
                $caminhoTemp = 'certificates/' . $nomeTemp;
                Storage::putFileAs('certificates', $arquivo, $nomeTemp);

                // Determina a senha para validação
                $senhaNova = $request->input('nfe_cert_password');
                $senhaAtual = Configuracao::get('nfe_cert_password');

                $senhaParaValidar = !empty($senhaNova) ? $senhaNova : $senhaAtual;

                if (empty($senhaParaValidar)) {
                    Storage::delete($caminhoTemp);
                    return back()->withErrors('É necessário fornecer a senha para validar o novo certificado.');
                }

                // Valida o certificado
                try {
                    $this->validarCertificado($caminhoTemp, $senhaParaValidar);
                } catch (Exception $e) {
                    Storage::delete($caminhoTemp);
                    return back()->withErrors('Erro ao validar certificado: ' . $e->getMessage());
                }

                // Se validou, move para o local definitivo e salva o caminho
                if (Storage::exists($caminho)) {
                    Storage::delete($caminho);
                }
                Storage::move($caminhoTemp, $caminho);
                Configuracao::set('nfe_cert_path', $nomeArquivo, 'nfe', 'file', 'Caminho do certificado digital');

                // Salva o conteúdo do certificado no banco de dados (para persistência no Heroku)
                try {
                    $certContent = Storage::get($caminho);
                    Configuracao::set('nfe_cert_data', base64_encode($certContent), 'nfe', 'longtext', 'Conteúdo do certificado digital (Base64)');
                    Log::info('Conteúdo do certificado salvo no banco de dados (ConfiguracaoController).');
                } catch (Exception $e) {
                    Log::error('Erro ao salvar conteúdo do certificado no banco (ConfiguracaoController): ' . $e->getMessage());
                }

                // Salva a senha validada
                Configuracao::set('nfe_cert_password', $senhaParaValidar, 'nfe', 'password', 'Senha do certificado digital');
                Log::info('Senha do certificado (novo) salva com sucesso.');
            }
            // 2. Se NÃO tem arquivo novo, mas tem senha nova, valida e atualiza
            elseif ($request->filled('nfe_cert_password')) {
                $senhaNova = $request->input('nfe_cert_password');

                // Ignora se for a máscara de senha existente
                if ($senhaNova === '********') {
                    // Não faz nada, mantém a atual
                } else {
                    $certPath = 'certificates/' . Configuracao::get('nfe_cert_path', 'certificado.pfx');

                    // Se o certificado existe, valida a nova senha contra ele
                    if (Storage::exists($certPath)) {
                        try {
                            $this->validarCertificado($certPath, $senhaNova);
                        } catch (Exception $e) {
                            return back()->withErrors('A nova senha fornecida é inválida para o certificado atual: ' . $e->getMessage());
                        }
                    }

                    // Salva a nova senha
                    Configuracao::set('nfe_cert_password', $senhaNova, 'nfe', 'password', 'Senha do certificado digital');
                    Log::info('Senha do certificado (existente) atualizada com sucesso.');
                }
            }

            // 3. Salva os demais campos da NFe explicitamente
            $camposNFe = [
                'nfe_razao_social' => 'text',
                'nfe_nome_fantasia' => 'text',
                'nfe_cnpj' => 'text',
                'nfe_ie' => 'text',
                'nfe_crt' => 'text',
                'nfe_ambiente' => 'number',
                'nfe_serie' => 'number',
                'nfe_ultimo_numero' => 'number',
                'nfe_csc' => 'text',
                'nfe_csc_id' => 'text',
            ];

            foreach ($camposNFe as $chave => $tipo) {
                if ($request->has($chave)) {
                    $valor = $request->input($chave);

                    // Limpeza específica
                    if ($chave === 'nfe_cnpj') {
                        $valor = preg_replace('/[^0-9]/', '', $valor);
                    }

                    // Validação de IE
                    if ($chave === 'nfe_ie') {
                        $valor = trim($valor);
                        if (!empty($valor) && !preg_match('/^([0-9]{2,14}|ISENTO)$/i', $valor)) {
                            return back()->withErrors("A Inscrição Estadual (IE) deve conter apenas números (2-14 dígitos) ou a palavra 'ISENTO'.");
                        }
                        $valor = strtoupper($valor);
                    }

                    Configuracao::set($chave, $valor, 'nfe', $tipo);
                }
            }

            LogService::registrar('Configuração', 'Atualizar', 'Configurações de NF-e atualizadas');
            return redirect()->route('configuracoes.index')->with('success', 'Configurações de NF-e salvas com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro em storeNFe: ' . $e->getMessage());
            return back()->withErrors('Erro ao salvar configurações de NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Inicializa configurações padrão
     */
    protected function inicializarConfiguracoes()
    {
        $configuracoesPadrao = [
            // Configurações Gerais
            ['chave' => 'empresa_nome', 'valor' => env('NFE_RAZAO_SOCIAL', 'JBTECH Informática'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'Nome da empresa'],
            ['chave' => 'empresa_cnpj', 'valor' => env('NFE_CNPJ', '54819910000120'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'CNPJ da empresa'],
            ['chave' => 'empresa_telefone', 'valor' => env('NFE_TELEFONE', '24981132097'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'Telefone da empresa'],
            ['chave' => 'empresa_email', 'valor' => env('NFE_EMAIL', 'informatica.jbtech@gmail.com'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'Email da empresa'],
            ['chave' => 'empresa_endereco', 'valor' => env('NFE_ENDERECO_LOGRADOURO', 'Rua Willy Faulstich'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'Endereço da empresa'],
            ['chave' => 'empresa_numero', 'valor' => env('NFE_ENDERECO_NUMERO', '252'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'Número do endereço'],
            ['chave' => 'empresa_bairro', 'valor' => env('NFE_ENDERECO_BAIRRO', 'Centro'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'Bairro'],
            ['chave' => 'empresa_cidade', 'valor' => env('NFE_ENDERECO_MUNICIPIO', 'Resende'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'Cidade'],
            ['chave' => 'empresa_uf', 'valor' => env('NFE_UF', 'RJ'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'UF'],
            ['chave' => 'empresa_cep', 'valor' => env('NFE_CEP', '27520000'), 'grupo' => 'geral', 'tipo' => 'text', 'descricao' => 'CEP'],

            // Configurações Produtos
            ['chave' => 'produtos_metodo_lucro', 'valor' => 'markup', 'grupo' => 'produtos', 'tipo' => 'select', 'descricao' => 'Método de Cálculo de Lucro'],

            // Configurações Financeiro
            ['chave' => 'financeiro_taxa_debito', 'valor' => '1.99', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Débito (%)'],
            ['chave' => 'financeiro_taxa_pix', 'valor' => '0.99', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa PIX (%)'],
            ['chave' => 'financeiro_taxa_boleto_fixa', 'valor' => '2.50', 'grupo' => 'financeiro', 'tipo' => 'money', 'descricao' => 'Taxa Boleto (R$)'],
            ['chave' => 'financeiro_taxa_boleto_porcentagem', 'valor' => '0.00', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Boleto (%)'],

            // Taxas Cartão (1x a 12x)
            ['chave' => 'financeiro_taxa_cartao_1x', 'valor' => '3.19', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 1x (%)'],
            ['chave' => 'financeiro_taxa_cartao_2x', 'valor' => '4.59', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 2x (%)'],
            ['chave' => 'financeiro_taxa_cartao_3x', 'valor' => '5.99', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 3x (%)'],
            ['chave' => 'financeiro_taxa_cartao_4x', 'valor' => '7.39', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 4x (%)'],
            ['chave' => 'financeiro_taxa_cartao_5x', 'valor' => '8.79', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 5x (%)'],
            ['chave' => 'financeiro_taxa_cartao_6x', 'valor' => '10.19', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 6x (%)'],
            ['chave' => 'financeiro_taxa_cartao_7x', 'valor' => '11.59', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 7x (%)'],
            ['chave' => 'financeiro_taxa_cartao_8x', 'valor' => '12.99', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 8x (%)'],
            ['chave' => 'financeiro_taxa_cartao_9x', 'valor' => '14.39', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 9x (%)'],
            ['chave' => 'financeiro_taxa_cartao_10x', 'valor' => '15.79', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 10x (%)'],
            ['chave' => 'financeiro_taxa_cartao_11x', 'valor' => '17.19', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 11x (%)'],
            ['chave' => 'financeiro_taxa_cartao_12x', 'valor' => '18.59', 'grupo' => 'financeiro', 'tipo' => 'number', 'descricao' => 'Taxa Crédito 12x (%)'],

            // Configurações NF-e
            ['chave' => 'nfe_ambiente', 'valor' => env('NFE_AMBIENTE', '2'), 'grupo' => 'nfe', 'tipo' => 'number', 'descricao' => 'Ambiente (1=Produção, 2=Homologação)'],
            ['chave' => 'nfe_razao_social', 'valor' => env('NFE_RAZAO_SOCIAL', 'JBTECH Informática'), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'Razão Social'],
            ['chave' => 'nfe_nome_fantasia', 'valor' => env('NFE_NOME_FANTASIA', 'JBTECH'), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'Nome Fantasia'],
            ['chave' => 'nfe_cnpj', 'valor' => env('NFE_CNPJ', '54819910000120'), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'CNPJ'],
            ['chave' => 'nfe_ie', 'valor' => env('NFE_IE', ''), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'Inscrição Estadual'],
            ['chave' => 'nfe_crt', 'valor' => env('NFE_CRT', '3'), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'CRT (1=Simples, 2=Simples Excesso, 3=Normal)'],
            ['chave' => 'nfe_cert_path', 'valor' => env('NFE_CERT_PATH', 'certificado.pfx'), 'grupo' => 'nfe', 'tipo' => 'file', 'descricao' => 'Caminho do certificado'],
            ['chave' => 'nfe_cert_password', 'valor' => '', 'grupo' => 'nfe', 'tipo' => 'password', 'descricao' => 'Senha do certificado'],
            ['chave' => 'nfe_serie', 'valor' => env('NFE_SERIE', '1'), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'Série da NF-e'],
            ['chave' => 'nfe_csc', 'valor' => env('NFE_CSC', ''), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'CSC (Código de Segurança do Contribuinte)'],
            ['chave' => 'nfe_csc_id', 'valor' => env('NFE_CSC_ID', ''), 'grupo' => 'nfe', 'tipo' => 'text', 'descricao' => 'ID do CSC'],

            // Configurações Email
            ['chave' => 'email_driver', 'valor' => env('MAIL_MAILER', 'smtp'), 'grupo' => 'email', 'tipo' => 'text', 'descricao' => 'Driver de email'],
            ['chave' => 'email_host', 'valor' => env('MAIL_HOST', 'smtp.gmail.com'), 'grupo' => 'email', 'tipo' => 'text', 'descricao' => 'Servidor SMTP'],
            ['chave' => 'email_porta', 'valor' => env('MAIL_PORT', '587'), 'grupo' => 'email', 'tipo' => 'number', 'descricao' => 'Porta SMTP'],
            ['chave' => 'email_usuario', 'valor' => env('MAIL_USERNAME', ''), 'grupo' => 'email', 'tipo' => 'text', 'descricao' => 'Usuário'],
            ['chave' => 'email_senha', 'valor' => '', 'grupo' => 'email', 'tipo' => 'password', 'descricao' => 'Senha'],
            ['chave' => 'email_criptografia', 'valor' => env('MAIL_ENCRYPTION', 'tls'), 'grupo' => 'email', 'tipo' => 'text', 'descricao' => 'Criptografia (tls/ssl)'],
            ['chave' => 'email_remetente_nome', 'valor' => env('MAIL_FROM_NAME', 'JBTECH'), 'grupo' => 'email', 'tipo' => 'text', 'descricao' => 'Nome do remetente'],
            ['chave' => 'email_remetente_email', 'valor' => env('MAIL_FROM_ADDRESS', 'informatica.jbtech@gmail.com'), 'grupo' => 'email', 'tipo' => 'text', 'descricao' => 'Email do remetente'],

            // Configurações Sistema
            ['chave' => 'sistema_timezone', 'valor' => env('APP_TIMEZONE', 'America/Sao_Paulo'), 'grupo' => 'sistema', 'tipo' => 'text', 'descricao' => 'Fuso horário'],
            ['chave' => 'sistema_locale', 'valor' => env('APP_LOCALE', 'pt_BR'), 'grupo' => 'sistema', 'tipo' => 'text', 'descricao' => 'Idioma'],
            ['chave' => 'sistema_paginacao', 'valor' => '15', 'grupo' => 'sistema', 'tipo' => 'number', 'descricao' => 'Itens por página'],
        ];

        foreach ($configuracoesPadrao as $config) {
            $existing = Configuracao::where('chave', $config['chave'])->whereNull('user_id')->first();

            if (!$existing) {
                Configuracao::create([
                    'chave' => $config['chave'],
                    'user_id' => null,
                    'valor' => $config['valor'],
                    'grupo' => $config['grupo'],
                    'tipo' => $config['tipo'],
                    'descricao' => $config['descricao'],
                ]);
            } else {
                // Atualiza apenas metadados, preservando o valor definido pelo usuário
                $existing->update([
                    'grupo' => $config['grupo'],
                    'tipo' => $config['tipo'],
                    'descricao' => $config['descricao'],
                ]);
            }
        }
    }

    /**
     * Consulta CNPJ via BrasilAPI
     */
    public function consultaCnpj($cnpj)
    {
        try {
            // Remove caracteres não numéricos
            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

            if (strlen($cnpj) !== 14) {
                return response()->json(['error' => 'CNPJ inválido'], 400);
            }

            // Consulta na BrasilAPI
            $response = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

            if ($response->failed()) {
                return response()->json(['error' => 'Não foi possível consultar o CNPJ. Verifique se está correto.'], 400);
            }

            $data = $response->json();

            return response()->json($data);
        } catch (Exception $e) {
            Log::error('Erro ao consultar CNPJ: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno ao consultar CNPJ'], 500);
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

    /**
     * Valida certificado usando OpenSSL via linha de comando (workaround para OpenSSL 3.x)
     */
    protected function validarCertificadoViaOpenSSL($certPath, $senha)
    {
        // Tenta usar OpenSSL diretamente via linha de comando
        $opensslPath = $this->encontrarOpenSSL();
        if (!$opensslPath) {
            return false;
        }

        // Cria arquivo temporário com a senha
        $senhaFile = storage_path('app/temp_senha_' . uniqid() . '.txt');
        file_put_contents($senhaFile, $senha);

        try {
            // Tenta ler o certificado usando OpenSSL com provider legacy
            $opensslConfigPath = $this->configurarOpenSSLLegacy();

            // Tenta PRIMEIRO com a flag -legacy (assumindo OpenSSL 3.x e certificado antigo)
            // Adiciona -provider-path se OPENSSL_MODULES estiver definido
            $extraArgs = '';
            if (getenv('OPENSSL_MODULES')) {
                $extraArgs .= ' -provider-path "' . getenv('OPENSSL_MODULES') . '"';
            }

            $commandLegacy = sprintf(
                '"%s" pkcs12 -info -in "%s" -passin file:"%s" -noout -legacy -config "%s"%s 2>&1',
                $opensslPath,
                escapeshellarg($certPath),
                escapeshellarg($senhaFile),
                escapeshellarg($opensslConfigPath),
                $extraArgs
            );

            $output = [];
            $returnVar = 0;
            exec($commandLegacy, $output, $returnVar);

            // Se falhou, verifica se é porque a flag -legacy não existe (OpenSSL 1.x) ou outro erro
            if ($returnVar !== 0) {
                $outputStr = implode(' ', $output);

                // Se o erro for opção desconhecida, tenta sem a flag -legacy
                if (str_contains($outputStr, 'unknown option') || str_contains($outputStr, 'bad flag') || str_contains($outputStr, 'Unrecognized flag')) {
                    $commandStandard = sprintf(
                        '"%s" pkcs12 -info -in "%s" -passin file:"%s" -noout 2>&1',
                        $opensslPath,
                        escapeshellarg($certPath),
                        escapeshellarg($senhaFile)
                    );

                    $output = [];
                    $returnVar = 0;
                    exec($commandStandard, $output, $returnVar);
                }
            }

            // Remove arquivo temporário
            @unlink($senhaFile);

            // Se o comando retornou 0, o certificado é válido
            if ($returnVar === 0) {
                return true;
            }

            Log::warning('Falha na validação OpenSSL (CLI): ' . implode(" | ", $output));
            return false;
        } catch (\Exception $e) {
            Log::error('Exceção na validação OpenSSL (CLI): ' . $e->getMessage());
            @unlink($senhaFile);
            return false;
        }
    }

    /**
     * Encontra o caminho do executável OpenSSL
     */
    protected function encontrarOpenSSL()
    {
        // Tenta encontrar OpenSSL em locais comuns
        $possiveisCaminhos = [
            'C:\\Program Files\\Git\\mingw64\\bin\\openssl.exe', // Prioridade para mingw64 (mais compatível com módulos no Windows)
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
            // No Windows, verifica se o arquivo existe antes de tentar executar para evitar delay
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

        // Tenta encontrar no PATH
        $output = [];
        $returnVar = 0;
        exec('openssl version 2>&1', $output, $returnVar);
        if ($returnVar === 0) {
            return 'openssl';
        }

        return null;
    }

    /**
     * Converte certificado legado para formato moderno compatível com OpenSSL 3
     */
    protected function converterCertificadoLegacy($caminho, $senha)
    {
        // Garante que o ambiente esteja configurado
        $this->configurarOpenSSLLegacy();

        $opensslPath = $this->encontrarOpenSSL();
        if (!$opensslPath) {
            throw new Exception('OpenSSL não encontrado no sistema.');
        }

        $certPath = storage_path('app/' . $caminho);
        // Normaliza caminhos
        $certPath = str_replace('\\', '/', $certPath);

        // Arquivos temporários
        $pemPath = $certPath . '.pem';
        $pfxPath = $certPath . '.converted.pfx';

        // Cria arquivo temporário com a senha
        $senhaFile = storage_path('app/temp_senha_' . uniqid() . '.txt');
        file_put_contents($senhaFile, $senha);
        $senhaFile = str_replace('\\', '/', $senhaFile);

        try {
            // Adiciona -provider-path se OPENSSL_MODULES estiver definido
            $extraArgs = '';
            if (getenv('OPENSSL_MODULES')) {
                $extraArgs .= ' -provider-path "' . getenv('OPENSSL_MODULES') . '"';
            }

            // --- ETAPA 1: Extrair PFX (Legacy) para PEM ---
            // Usamos -nodes para não criptografar a chave privada no arquivo temporário PEM

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

                // Retry 1: Senha direta se erro de BIO (problema no Windows)
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

                // Retry 2: Sem flag -legacy (caso seja OpenSSL antigo ou erro de flag)
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

                    // Retry 2.1: Sem flag -legacy e com senha direta
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

            // --- ETAPA 2: Criar novo PFX a partir do PEM ---
            // O novo PFX usará os algoritmos padrão do OpenSSL atual (modernos)

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

            // Retry Export: Senha direta se erro de BIO
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
                // Substitui o arquivo original pelo convertido
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
            if (isset($pemPath) && file_exists($pemPath)) @unlink($pemPath);
            if (isset($pfxPath) && file_exists($pfxPath)) @unlink($pfxPath);
            throw $e;
        }
    }

    /**
     * Valida o certificado digital e sua senha
     */
    protected function validarCertificado($caminho, $senha)
    {
        try {
            $certPath = storage_path('app/' . $caminho);

            if (!file_exists($certPath)) {
                throw new Exception('Certificado não encontrado.');
            }

            if (empty($senha)) {
                throw new Exception('Senha do certificado não fornecida.');
            }

            // Verifica se o arquivo é válido
            $certContent = file_get_contents($certPath);
            if (empty($certContent)) {
                throw new Exception('O arquivo do certificado está vazio ou corrompido.');
            }

            // Configura OpenSSL para usar provider legacy (OpenSSL 3.x)
            $this->configurarOpenSSLLegacy();

            // Tenta usar a biblioteca NFePHP (abordagem principal)
            try {
                $certificate = Certificate::readPfx($certContent, $senha);

                // Se chegou aqui, funcionou!
                return true;
            } catch (\Exception $e) {
                $msg = $e->getMessage();

                // Verifica se é erro de senha
                if (str_contains($msg, 'Mac verify failure') || str_contains($msg, 'PKCS12_parse') || str_contains($msg, 'bad decrypt')) {
                    throw new Exception('Senha do certificado incorreta.');
                }

                // Verifica se é erro de algoritmo legado (OpenSSL 3.x)
                if (str_contains($msg, 'digital envelope') || str_contains($msg, 'unsupported')) {

                    // Tenta converter
                    try {
                        $this->converterCertificadoLegacy($caminho, $senha);

                        // Se converteu, tenta ler novamente
                        $certContent = file_get_contents($certPath);
                        Certificate::readPfx($certContent, $senha);
                        return true;
                    } catch (\Exception $convEx) {
                        // Se falhou a conversão, relança o erro da conversão se for de senha
                        if (str_contains($convEx->getMessage(), 'Senha')) {
                            throw $convEx;
                        }

                        // Caso contrário, lança erro detalhado
                        throw new Exception('O certificado utiliza criptografia antiga não suportada pelo OpenSSL 3.x e a conversão automática falhou: ' . $convEx->getMessage());
                    }
                }

                // Outros erros
                throw $e;
            }
        } catch (Exception $e) {
            // Log do erro real
            Log::error('Erro validação certificado: ' . $e->getMessage());
            throw $e;
        }
    }
}
