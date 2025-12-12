<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Configuracao extends Model
{
    use HasFactory;

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
     * Obtém o valor de uma configuração
     * @param string $chave Chave da configuração
     * @param mixed $default Valor padrão se não encontrar
     * @param int|null $userId ID do usuário (null para configuração global, 'auto' para usar usuário autenticado)
     */
    public static function get($chave, $default = null, $userId = null)
    {
        try {
            // Configurações que são por usuário
            $configuracoesPorUsuario = ['produtos_edicao_inline'];
            $ehConfiguracaoPorUsuario = in_array($chave, $configuracoesPorUsuario);
            
            // Se for configuração por usuário e userId não foi informado, tenta usar o usuário autenticado
            if ($ehConfiguracaoPorUsuario && $userId === null && auth()->check()) {
                $userId = auth()->id();
            }
            
            // Se não for configuração por usuário, força userId = null (global)
            if (!$ehConfiguracaoPorUsuario) {
                $userId = null;
            }
            
            // Limpa cache antes de buscar
            $cacheKey = $userId ? "configuracao_{$chave}_user_{$userId}" : "configuracao_{$chave}";
            Cache::forget($cacheKey);
            
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
            // Se userId não foi informado, tenta usar o usuário autenticado
            if ($userId === null && auth()->check()) {
                $userId = auth()->id();
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
