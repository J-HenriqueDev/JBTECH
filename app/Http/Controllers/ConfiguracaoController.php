<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use NFePHP\Common\Certificate;
use Exception;

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
     * Testa o certificado digital antes de salvar
     */
    public function testarCertificado(Request $request)
    {
        try {
            $request->validate([
                'certificado' => 'required|file|mimes:pfx',
                'senha' => 'required|string',
            ]);

            $arquivo = $request->file('certificado');
            $senha = $request->input('senha');

            // Salva temporariamente para testar
            $nomeTemp = 'certificado_temp_' . time() . '.pfx';
            $caminhoTemp = 'certificates/' . $nomeTemp;
            
            // Cria o diretório se não existir
            if (!Storage::exists('certificates')) {
                Storage::makeDirectory('certificates');
            }
            
            // Salva temporariamente
            Storage::putFileAs('certificates', $arquivo, $nomeTemp);
            
            try {
                // Valida o certificado
                $this->validarCertificado($caminhoTemp, $senha);
                
                // Remove o arquivo temporário após validação bem-sucedida
                Storage::delete($caminhoTemp);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Certificado e senha válidos!'
                ]);
            } catch (Exception $e) {
                // Remove o arquivo temporário em caso de erro
                if (Storage::exists($caminhoTemp)) {
                    Storage::delete($caminhoTemp);
                }
                throw $e;
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Salva as configurações
     */
    public function store(Request $request)
    {
        try {
            $grupo = $request->input('grupo', 'geral');
            $dados = $request->except(['_token', 'grupo']);
            
            // Debug: verifica se a senha está chegando
            Log::info('Salvando configurações', [
                'grupo' => $grupo,
                'tem_certificado' => $request->hasFile('nfe_certificado'),
                'senha_fornecida' => $request->filled('nfe_cert_password'),
                'senha_valor' => $request->filled('nfe_cert_password') ? '***' : 'vazia',
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

            // Tratamento especial para upload de certificado
            if ($request->hasFile('nfe_certificado')) {
                $arquivo = $request->file('nfe_certificado');
                
                // Valida o tipo de arquivo
                if ($arquivo->getClientOriginalExtension() !== 'pfx') {
                    return back()->withErrors('O arquivo deve ser um certificado digital no formato PFX.');
                }
                
                $nomeArquivo = 'certificado.pfx';
                $caminho = 'certificates/' . $nomeArquivo;
                
                // Cria o diretório se não existir
                if (!Storage::exists('certificates')) {
                    Storage::makeDirectory('certificates');
                }
                
                // Salva temporariamente para validar antes de substituir
                $nomeTemp = 'certificado_temp_' . time() . '.pfx';
                $caminhoTemp = 'certificates/' . $nomeTemp;
                Storage::putFileAs('certificates', $arquivo, $nomeTemp);
                
                // Obtém a senha (nova ou atual)
                $senhaNova = $request->input('nfe_cert_password');
                $senhaAtual = Configuracao::get('nfe_cert_password', '');
                
                // Determina qual senha usar
                if (!empty($senhaNova)) {
                    // Se forneceu senha nova, usa ela
                    $senha = $senhaNova;
                } elseif (!empty($senhaAtual)) {
                    // Se não forneceu mas existe senha atual, usa a atual
                    $senha = $senhaAtual;
                } else {
                    // Se não forneceu e não existe senha atual, retorna erro
                    Storage::delete($caminhoTemp);
                    return back()->withErrors('É necessário fornecer a senha do certificado ao importar um novo certificado.');
                }
                
                // OBRIGATÓRIO: Valida o certificado ANTES de salvar
                try {
                    $this->validarCertificado($caminhoTemp, $senha);
                } catch (Exception $e) {
                    Storage::delete($caminhoTemp);
                    return back()->withErrors('Erro ao validar certificado: ' . $e->getMessage() . ' Por favor, verifique o certificado e a senha antes de tentar novamente.');
                }
                
                // Se passou na validação, substitui o arquivo antigo
                if (Storage::exists($caminho)) {
                    Storage::delete($caminho);
                }
                
                // Move o arquivo temporário para o nome final
                Storage::move($caminhoTemp, $caminho);
                
                Configuracao::set('nfe_cert_path', $nomeArquivo, 'nfe', 'file', 'Caminho do certificado digital');
                
                // SEMPRE salva a senha quando um certificado é importado (já foi validada acima)
                // Se foi fornecida nova senha, salva a nova. Se não, mantém a atual.
                $senhaParaSalvar = !empty($senhaNova) ? trim($senhaNova) : (empty($senhaAtual) ? '' : trim($senhaAtual));
                
                if (empty($senhaParaSalvar)) {
                    Storage::delete($caminho);
                    return back()->withErrors('É necessário fornecer a senha do certificado ao importar.');
                }
                
                // Salva a senha
                try {
                    $resultado = Configuracao::set('nfe_cert_password', $senhaParaSalvar, 'nfe', 'password', 'Senha do certificado digital');
                    
                    // Verifica se foi salvo corretamente - busca direto do banco
                    $configSalva = Configuracao::where('chave', 'nfe_cert_password')->first();
                    if (!$configSalva || $configSalva->valor !== $senhaParaSalvar) {
                        Log::error('Erro ao salvar senha do certificado no banco de dados', [
                            'senha_fornecida' => !empty($senhaNova) ? 'nova' : 'atual',
                            'senha_length' => strlen($senhaParaSalvar),
                            'resultado_id' => $resultado ? $resultado->id : 'null',
                            'valor_salvo' => $configSalva ? $configSalva->valor : 'null',
                            'valor_esperado' => $senhaParaSalvar
                        ]);
                        Storage::delete($caminho);
                        return back()->withErrors('Erro ao salvar a senha do certificado no banco de dados. Verifique os logs para mais detalhes.');
                    }
                    
                    Log::info('Senha do certificado salva com sucesso', [
                        'senha_length' => strlen($senhaParaSalvar),
                        'config_id' => $resultado->id,
                        'valor_verificado' => $configSalva->valor
                    ]);
                } catch (\Exception $e) {
                    Log::error('Exceção ao salvar senha do certificado', [
                        'erro' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    Storage::delete($caminho);
                    return back()->withErrors('Erro ao salvar a senha do certificado: ' . $e->getMessage());
                }
            }

            foreach ($dados as $chave => $valor) {
                // Ignora o campo de upload de arquivo (já foi processado)
                if ($chave === 'nfe_certificado' || $chave === 'interface_logo') {
                    continue;
                }


                // Valida senha do certificado se foi alterada
                if ($chave === 'nfe_cert_password') {
                    // Se um novo certificado foi enviado, a senha já foi processada acima
                    if ($request->hasFile('nfe_certificado')) {
                        continue; // Já foi processado no bloco acima
                    }
                    
                    // Se a senha está vazia, mantém a atual (se existir)
                    if (empty($valor) || trim($valor) === '') {
                        $senhaAtual = Configuracao::get('nfe_cert_password');
                        if (!empty($senhaAtual)) {
                            continue; // Mantém a senha atual, não atualiza
                        }
                        // Se está vazia e não existe uma configurada, não salva
                        continue;
                    }
                    
                    // Valida a senha se foi fornecida (usando certificado atual)
                    $certPath = 'certificates/' . Configuracao::get('nfe_cert_path', 'certificado.pfx');
                    
                    if (!Storage::exists($certPath)) {
                        // Se não tem certificado, apenas salva a senha sem validar
                        Log::info('Salvando senha sem validar (certificado não encontrado)', ['chave' => $chave]);
                    } else {
                        // Valida a senha com o certificado existente
                        try {
                            $this->validarCertificado($certPath, $valor);
                        } catch (Exception $e) {
                            return back()->withErrors('Erro ao validar senha do certificado: ' . $e->getMessage());
                        }
                    }
                    
                    // Salva a senha (validada ou não, dependendo se tem certificado)
                    $senhaLimpa = trim($valor);
                    if (empty($senhaLimpa)) {
                        continue; // Não salva senha vazia
                    }
                    
                    $resultado = Configuracao::set('nfe_cert_password', $senhaLimpa, 'nfe', 'password', 'Senha do certificado digital');
                    
                    // Verifica se foi salvo corretamente - busca direto do banco
                    $configSalva = Configuracao::where('chave', 'nfe_cert_password')->first();
                    if (!$configSalva || $configSalva->valor !== $senhaLimpa) {
                        Log::error('Erro ao salvar senha do certificado no banco de dados', [
                            'chave' => $chave,
                            'valor_length' => strlen($senhaLimpa),
                            'resultado_id' => $resultado ? $resultado->id : 'null',
                            'valor_salvo' => $configSalva ? $configSalva->valor : 'null',
                            'valor_esperado' => $senhaLimpa
                        ]);
                        return back()->withErrors('Erro ao salvar a senha do certificado no banco de dados. Por favor, tente novamente.');
                    }
                    
                    Log::info('Senha do certificado salva com sucesso', [
                        'chave' => $chave,
                        'senha_length' => strlen($senhaLimpa),
                        'config_id' => $resultado->id
                    ]);
                    
                    // Já foi salvo, não precisa processar novamente no loop
                    continue;
                }
                
                // Ignora campos vazios de senha (para não sobrescrever) - exceto nfe_cert_password que já foi tratado
                if ((str_contains($chave, 'senha') || str_contains($chave, 'password')) && empty($valor)) {
                    continue;
                }

                // Determina o grupo e tipo baseado na chave
                $configGrupo = $grupo;
                $configTipo = 'text';
                
                if (str_starts_with($chave, 'nfe_')) {
                    $configGrupo = 'nfe';
                } elseif (str_starts_with($chave, 'email_')) {
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
                } elseif (str_contains($chave, 'ambiente') || str_contains($chave, 'porta') || str_contains($chave, 'paginacao') || 
                          str_contains($chave, 'desconto') || str_contains($chave, 'comissao') || str_contains($chave, 'limite') ||
                          str_contains($chave, 'prazo') || str_contains($chave, 'periodo') || str_contains($chave, 'estoque_minimo') ||
                          str_contains($chave, 'garantia')) {
                    $configTipo = 'number';
                } elseif (str_contains($chave, 'ativo') || str_contains($chave, 'habilitado') || str_contains($chave, 'edicao_inline') ||
                          str_contains($chave, 'exigir') || str_contains($chave, 'permitir') || str_contains($chave, 'gerar') ||
                          str_contains($chave, 'imprimir') || str_contains($chave, 'enviar') || str_contains($chave, 'alertar') ||
                          str_contains($chave, 'mostrar') || str_contains($chave, 'agrupar') || str_contains($chave, 'controle') ||
                          str_contains($chave, 'venda_estoque_negativo')) {
                    $configTipo = 'boolean';
                } elseif (str_contains($chave, 'email')) {
                    $configTipo = 'email';
                }

                // Remove formatação de campos numéricos antes de salvar
                if (str_contains($chave, 'cnpj') || str_contains($chave, 'cpf') || 
                    str_contains($chave, 'cep') || str_contains($chave, 'telefone')) {
                    $valor = preg_replace('/[^0-9]/', '', $valor);
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
            Configuracao::updateOrCreate(
                ['chave' => $config['chave']],
                [
                    'valor' => $config['valor'],
                    'grupo' => $config['grupo'],
                    'tipo' => $config['tipo'],
                    'descricao' => $config['descricao'],
                ]
            );
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
            
            // Comando para verificar o certificado usando provider legacy
            // Usa -legacy para forçar o uso de algoritmos legados
            $command = sprintf(
                '"%s" pkcs12 -info -in "%s" -passin file:"%s" -noout -legacy -config "%s" 2>&1',
                $opensslPath,
                escapeshellarg($certPath),
                escapeshellarg($senhaFile),
                escapeshellarg($opensslConfigPath)
            );
            
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);
            
            // Remove arquivo temporário
            @unlink($senhaFile);
            
            // Se o comando retornou 0, o certificado é válido
            return $returnVar === 0;
        } catch (\Exception $e) {
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
            'openssl',
            '/usr/bin/openssl',
            '/usr/local/bin/openssl',
            'C:\\OpenSSL-Win64\\bin\\openssl.exe',
            'C:\\OpenSSL-Win32\\bin\\openssl.exe',
        ];
        
        foreach ($possiveisCaminhos as $caminho) {
            $output = [];
            $returnVar = 0;
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
            
            // Configura OpenSSL para usar provider legacy (OpenSSL 3.x) ANTES de qualquer uso
            $this->configurarOpenSSLLegacy();
            
            // Método 1: Tenta validar via OpenSSL linha de comando (mais confiável para OpenSSL 3.x)
            if ($this->validarCertificadoViaOpenSSL($certPath, $senha)) {
                return true;
            }
            
            // Método 2: Tenta com openssl_pkcs12_read (método nativo do PHP)
            if (function_exists('openssl_pkcs12_read')) {
                $certData = null;
                $result = @openssl_pkcs12_read($certContent, $certData, $senha);
                if ($result && isset($certData['cert'])) {
                    // Certificado válido usando método nativo do PHP
                    return true;
                }
            }
            
            // Método 3: Tenta com NFePHP Certificate::readPfx
            try {
                $certificate = Certificate::readPfx($certContent, $senha);
                
                // Verifica se o certificado foi lido com sucesso
                if (!$certificate) {
                    throw new Exception('Não foi possível ler o certificado.');
                }
                
                return true;
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                
                // Trata erros específicos
                if (str_contains($errorMessage, 'mac verify failure') || 
                    str_contains($errorMessage, 'PKCS12') ||
                    str_contains($errorMessage, 'mac verify')) {
                    throw new Exception('Senha do certificado digital incorreta. Por favor, verifique a senha.');
                } elseif (str_contains($errorMessage, 'digital envelope') || 
                          str_contains($errorMessage, 'unsupported')) {
                    // Se ainda falhar, tenta uma última vez com OpenSSL linha de comando
                    if ($this->validarCertificadoViaOpenSSL($certPath, $senha)) {
                        return true;
                    }
                    
                    throw new Exception('Erro ao ler certificado com OpenSSL 3.x. O sistema tentou múltiplas abordagens (provider legacy, linha de comando, métodos nativos) mas ainda assim falhou. Isso pode indicar que o certificado precisa ser reexportado ou que há um problema de configuração do OpenSSL no servidor. Detalhes: ' . $errorMessage);
                } elseif (str_contains($errorMessage, 'bad decrypt')) {
                    throw new Exception('Senha do certificado incorreta ou certificado corrompido.');
                } else {
                    throw new Exception('Erro ao validar certificado: ' . $errorMessage);
                }
            }
        } catch (Exception $e) {
            // Se já é uma Exception com mensagem tratada, apenas relança
            if (str_starts_with($e->getMessage(), 'Senha') || 
                str_starts_with($e->getMessage(), 'Erro ao ler') ||
                str_starts_with($e->getMessage(), 'Erro ao validar')) {
                throw $e;
            }
            
            // Caso contrário, trata o erro genérico
            $errorMessage = 'Erro ao validar certificado: ' . $e->getMessage();
            throw new Exception($errorMessage);
        }
    }
}
