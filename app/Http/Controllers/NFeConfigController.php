<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use App\Services\LogService;
use App\Services\NFeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use NFePHP\Common\Certificate;
use Exception;

class NFeConfigController extends Controller
{
    /**
     * Exibe a página de configuração da NF-e
     */
    public function index()
    {
        // Verifica permissão (apenas admin)
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->withErrors('Acesso negado.');
        }

        // Obtém informações do certificado
        $certificadoInfo = null;
        try {
            $nfeService = new NFeService();
            $certificadoInfo = $nfeService->getCertificadoInfo();
        } catch (Exception $e) {
            // Ignora erro se não tiver certificado configurado ainda
            $certificadoInfo = ['erro' => $e->getMessage()];
        }

        LogService::registrar(
            'Configuração NF-e',
            'Visualizar',
            'Acessou a página de configurações de NF-e'
        );

        return view('content.nfe.config', compact('certificadoInfo'));
    }

    /**
     * Salva as configurações da NF-e
     */
    public function store(Request $request)
    {
        // Verifica permissão (apenas admin)
        if (!auth()->user()->isAdmin()) {
            return back()->withErrors('Acesso negado.');
        }

        try {
            Log::info('Iniciando NFeConfigController::store');

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
                    Log::info('Conteúdo do certificado salvo no banco de dados.');
                } catch (Exception $e) {
                    Log::error('Erro ao salvar conteúdo do certificado no banco: ' . $e->getMessage());
                }

                // Salva a senha validada
                Configuracao::set('nfe_cert_password', $senhaParaValidar, 'nfe', 'password', 'Senha do certificado digital');
                Log::info('Senha do certificado (novo) salva com sucesso.');
            }
            // 2. Se NÃO tem arquivo novo, mas tem senha nova, valida e atualiza
            elseif ($request->filled('nfe_cert_password')) {
                $senhaNova = $request->input('nfe_cert_password');

                // Ignora se for a máscara de senha
                if ($senhaNova === '********') {
                    Log::info('Senha recebida é a máscara (********), ignorando atualização.');
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

            $camposNFe = [
                'nfe_razao_social' => 'text',
                'nfe_nome_fantasia' => 'text',
                'nfe_cnpj' => 'text',
                'nfe_ie' => 'text',
                'nfe_crt' => 'text',
                'nfe_ambiente' => 'number',
                'nfe_serie' => 'number',
                'nfe_ultimo_numero' => 'number',
                'nfse_ultimo_numero' => 'number',
                'nfe_csc' => 'text',
                'nfe_csc_id' => 'text',
                'nfe_endereco_logradouro' => 'text',
                'nfe_endereco_numero' => 'text',
                'nfe_endereco_complemento' => 'text',
                'nfe_endereco_bairro' => 'text',
                'nfe_endereco_municipio' => 'text',
                'nfe_endereco_codigo_municipio' => 'text',
                'nfe_endereco_uf' => 'text',
                'nfe_cep' => 'text',
                'nfe_telefone' => 'text',
            ];

            foreach ($camposNFe as $chave => $tipo) {
                if ($request->has($chave)) {
                    $valor = $request->input($chave);

                    // Limpeza específica
                    if ($chave === 'nfe_cnpj') {
                        $valor = preg_replace('/[^0-9]/', '', $valor);
                    }

                    Configuracao::set($chave, $valor, 'nfe', $tipo);
                }
            }

            LogService::registrar('Configuração NF-e', 'Atualizar', 'Configurações de NF-e atualizadas');
            return redirect()->route('nfe.config')->with('success', 'Configurações de NF-e salvas com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro em NFeConfigController::store: ' . $e->getMessage());
            return back()->withErrors('Erro ao salvar configurações de NF-e: ' . $e->getMessage());
        }
    }

    /**
     * Testa o certificado e senha
     */
    public function testarCertificado(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        try {
            $senha = $request->input('senha');

            // Se não enviou arquivo, tenta usar o existente
            if (!$request->hasFile('certificado')) {
                $certFilename = Configuracao::get('nfe_cert_path', 'certificado.pfx');
                $caminhoRelativo = 'certificates/' . $certFilename;

                if (!Storage::exists($caminhoRelativo)) {
                    throw new Exception('É necessário selecionar um arquivo de certificado para testar.');
                }

                if (empty($senha)) {
                    $senha = Configuracao::get('nfe_cert_password');
                    if (empty($senha)) {
                        throw new Exception('Senha não fornecida e não encontrada no banco de dados.');
                    }
                }

                $this->validarCertificado($caminhoRelativo, $senha);

                Configuracao::set('nfe_cert_password', $senha, 'nfe', 'password', 'Senha do certificado digital');

                return response()->json([
                    'success' => true,
                    'message' => 'Certificado existente e senha válidos!'
                ]);
            }

            $arquivo = $request->file('certificado');

            // Validação manual da extensão
            $extensao = strtolower($arquivo->getClientOriginalExtension());
            if ($extensao !== 'pfx' && $extensao !== 'p12') {
                throw new Exception('O arquivo deve ser do tipo .pfx ou .p12');
            }

            // Salva temporariamente para testar
            $nomeTemp = 'certificado_temp_' . time() . '.pfx';
            $caminhoTemp = 'certificates/' . $nomeTemp;

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
     * Valida o certificado digital e sua senha
     */
    private function validarCertificado($caminhoRelativo, $senha)
    {
        try {
            $certPath = storage_path('app/' . $caminhoRelativo);

            if (!file_exists($certPath)) {
                throw new Exception('Certificado não encontrado: ' . $certPath);
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
                Log::warning('Falha na leitura direta do certificado (tentando conversão): ' . $msg);

                // Tenta converter para formato moderno se falhar a leitura direta
                // Isso cobre tanto erros de algoritmo ("digital envelope") quanto falsos "Mac verify failure" do OpenSSL 3
                try {
                    $this->converterCertificadoLegacy($caminhoRelativo, $senha);

                    // Se converteu, tenta ler novamente
                    $certContent = file_get_contents($certPath);
                    Certificate::readPfx($certContent, $senha);
                    return true;
                } catch (\Exception $convEx) {
                    Log::error('Falha na conversão do certificado: ' . $convEx->getMessage());

                    // Se falhou a conversão e o erro original ou da conversão indicava problema de senha/MAC,
                    // então muito provavelmente a senha está errada mesmo.
                    $erroSenha = false;
                    $msgsParaVerificar = [$msg, $convEx->getMessage()];

                    foreach ($msgsParaVerificar as $m) {
                        if (
                            str_contains($m, 'Mac verify failure') ||
                            str_contains($m, 'PKCS12_parse') ||
                            str_contains($m, 'bad decrypt') ||
                            str_contains($m, 'Error reading password') ||
                            str_contains($m, 'Verify error:invalid password')
                        ) {
                            $erroSenha = true;
                            break;
                        }
                    }

                    if ($erroSenha) {
                        throw new Exception('Senha do certificado incorreta.');
                    }

                    // Caso contrário, lança erro detalhado
                    throw new Exception('Não foi possível ler o certificado. Erro original: ' . $msg . '. Erro conversão: ' . $convEx->getMessage());
                }
            }
        } catch (Exception $e) {
            // Log do erro real
            Log::error('Erro validação certificado: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Configura o OpenSSL para usar provider legacy (necessário para OpenSSL 3.x)
     */
    private function configurarOpenSSLLegacy()
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
        putenv('OPENSSL_CONF=' . $opensslConfigPath);
        $_ENV['OPENSSL_CONF'] = $opensslConfigPath;
        $_SERVER['OPENSSL_CONF'] = $opensslConfigPath;

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
                Log::info('OPENSSL_MODULES configurado para: ' . $path);
                break;
            }
        }

        return $opensslConfigPath;
    }

    /**
     * Encontra o caminho do executável OpenSSL
     */
    private function encontrarOpenSSL()
    {
        // Tenta encontrar OpenSSL em locais comuns
        $possiveisCaminhos = [
            'C:\\xampp\\apache\\bin\\openssl.exe',
            'C:\\xampp\\php\\extras\\openssl\\openssl.exe',
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
            // No Windows, verifica se o arquivo existe antes de tentar executar
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
    private function converterCertificadoLegacy($caminhoRelativo, $senha)
    {
        Log::info('Iniciando conversão de certificado legacy.', ['caminho' => $caminhoRelativo]);

        // Garante que o ambiente esteja configurado
        $this->configurarOpenSSLLegacy();

        $opensslPath = $this->encontrarOpenSSL();
        Log::info('Executável OpenSSL encontrado.', ['path' => $opensslPath]);

        if (!$opensslPath) {
            Log::error('OpenSSL não encontrado no sistema.');
            throw new Exception('OpenSSL não encontrado no sistema.');
        }

        $certPath = storage_path('app/' . $caminhoRelativo);
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

            Log::info('Executando comando de extração.', ['command' => $commandExtract]);

            $output = [];
            $returnVar = 0;
            exec($commandExtract, $output, $returnVar);

            Log::info('Resultado da extração.', ['returnVar' => $returnVar, 'output' => $output]);

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
                    Log::info('Retentativa 1 (Senha direta).', ['command' => $commandExtract]);
                    $output = [];
                    $returnVar = 0;
                    exec($commandExtract, $output, $returnVar);
                    $outputStr = implode(' ', $output);
                    Log::info('Resultado Retentativa 1.', ['returnVar' => $returnVar, 'output' => $output]);
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
                    Log::info('Retentativa 2 (Sem -legacy).', ['command' => $commandExtract]);
                    $output = [];
                    $returnVar = 0;
                    exec($commandExtract, $output, $returnVar);
                    $outputStr = implode(' ', $output);
                    Log::info('Resultado Retentativa 2.', ['returnVar' => $returnVar, 'output' => $output]);
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
}
