<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\Loggable;

class Configuracao extends Model
{
    use HasFactory, Loggable;

    protected $table = 'configuracoes';

    protected $fillable = [
        'chave',
        'valor',
        'grupo',
        'tipo',
        'descricao',
        'user_id',
    ];

    /**
     * Lista de configurações que são específicas por usuário
     */
    protected static $configuracoesPorUsuario = [
        'produtos_edicao_inline'
    ];

    /**
     * Obtém o valor de uma configuração
     * @param string $chave Chave da configuração
     * @param mixed $default Valor padrão se não encontrar
     * @param int|null $userId ID do usuário (null para configuração global, 'auto' para usar usuário autenticado)
     */
    public static function get($chave, $default = null, $userId = null)
    {
        try {
            // Verifica se é configuração por usuário
            $ehConfiguracaoPorUsuario = in_array($chave, self::$configuracoesPorUsuario);

            // Se for configuração por usuário e userId não foi informado, tenta usar o usuário autenticado
            if ($ehConfiguracaoPorUsuario && $userId === null && auth()->check()) {
                $userId = auth()->id();
                public static function getTempoRestanteSoneca()
    {
        $nextQuery = self::get('nfe_next_dfe_query');
        if (!$nextQuery) return 0;

        $nextQueryDate = \Carbon\Carbon::parse($nextQuery);
        if (now()->lt($nextQueryDate)) {
            return (int) ceil(now()->diffInMinutes($nextQueryDate));
        }

        return 0;
    }
}

            // Se não for configuração por usuário, força userId = null (global)
            if (!$ehConfiguracaoPorUsuario) {
                $userId = null;
            }

            // Busca primeiro a configuração do usuário (se aplicável), depois a global
            $config = null;
            if ($userId) {
                $config = self::where('chave', $chave)
                    ->where('user_id', $userId)
                    ->first();
            }

            // Se não encontrou configuração do usuário, busca global (user_id = null)
            if (!$config) {
                $config = self::where('chave', $chave)
                    ->whereNull('user_id')
                    ->first();
            }

            // FALLBACK: Se ainda não encontrou e não é configuração por usuário,
            // tenta encontrar qualquer registro com essa chave (pode ter sido salvo com user_id indevidamente)
            if (!$config && !$ehConfiguracaoPorUsuario) {
                $configFallback = self::where('chave', $chave)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($configFallback) {
                    Log::warning("Configuracao::get: Chave '{$chave}' encontrada apenas com user_id definido (recuperação de falha).", [
                        'chave' => $chave,
                        'user_id_encontrado' => $configFallback->user_id
                    ]);
                    $config = $configFallback;
                }
            }

            // Log específico para debug da senha do certificado
            if ($chave === 'nfe_cert_password') {
                if ($config) {
                    // Log::info('Configuracao::get nfe_cert_password encontrada', ['valor_length' => strlen($config->valor), 'user_id_busca' => $userId]);
                } else {
                    Log::warning('Configuracao::get nfe_cert_password NÃO encontrada', ['user_id_busca' => $userId, 'eh_por_usuario' => $ehConfiguracaoPorUsuario]);
                }
            }

            if ($config && $config->valor !== null) {
                // Para senhas, retorna mesmo se vazio (pode ser senha vazia válida)
                if ($config->tipo === 'password') {
                    return $config->valor;
                }
                // Para boolean, converte string para boolean e retorna como string '1' ou '0'
                if ($config->tipo === 'boolean') {
                    $valor = filter_var($config->valor, FILTER_VALIDATE_BOOLEAN);
                    return $valor ? '1' : '0';
                }
                // Para outros tipos, retorna apenas se não estiver vazio
                return $config->valor !== '' ? $config->valor : $default;
            }
            return $default;
        } catch (\Exception $e) {
            Log::error('Erro ao buscar configuração', ['chave' => $chave, 'erro' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Define o valor de uma configuração
     * @param string $chave Chave da configuração
     * @param mixed $valor Valor da configuração
     * @param string $grupo Grupo da configuração
     * @param string $tipo Tipo da configuração
     * @param string|null $descricao Descrição da configuração
     * @param int|null $userId ID do usuário (null para configuração global ou usar usuário autenticado)
     */
    public static function set($chave, $valor, $grupo = 'geral', $tipo = 'text', $descricao = null, $userId = null)
    {
        try {
            // Verifica se é configuração por usuário
            $ehConfiguracaoPorUsuario = in_array($chave, self::$configuracoesPorUsuario);

            // Se for configuração por usuário e userId não foi informado, tenta usar o usuário autenticado
            if ($ehConfiguracaoPorUsuario && $userId === null && auth()->check()) {
                $userId = auth()->id();
            }

            // Se NÃO for configuração por usuário, força userId = null (global)
            if (!$ehConfiguracaoPorUsuario) {
                $userId = null;
            }

            // Log específico para debug da senha do certificado
            if ($chave === 'nfe_cert_password') {
                Log::info('Configuracao::set nfe_cert_password', [
                    'valor_length' => strlen($valor),
                    'user_id' => $userId,
                    'grupo' => $grupo,
                    'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
                ]);

                // PREVENÇÃO DE PERDA DE SENHA: Não permite salvar senha vazia para certificado
                if (empty($valor)) {
                    Log::error('BLOQUEIO: Tentativa de salvar senha do certificado vazia impedida.', [
                        'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
                    ]);
                    return null;
                }
            }

            $resultado = self::updateOrCreate(
                [
                    'chave' => $chave,
                    'user_id' => $userId
                ],
                [
                    'valor' => $valor !== null ? (string)$valor : '',
                    'grupo' => $grupo,
                    'tipo' => $tipo,
                    'descricao' => $descricao,
                ]
            );

            // Limpa cache se existir
            $cacheKey = $userId ? "configuracao_{$chave}_user_{$userId}" : "configuracao_{$chave}";
            Cache::forget($cacheKey);

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Erro ao salvar configuração', [
                'chave' => $chave,
                'user_id' => $userId,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtém todas as configurações de um grupo
     */
    public static function getByGrupo($grupo)
    {
        return self::where('grupo', $grupo)->get()->pluck('valor', 'chave')->toArray();
    }
}
