<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoPDV extends Model
{
    use HasFactory;

    protected $table = 'configuracao_pdv';

    protected $fillable = [
        'chave',
        'valor',
        'tipo',
        'descricao',
    ];

    public static function get($chave, $default = null)
    {
        $config = self::where('chave', $chave)->first();
        
        if (!$config) {
            return $default;
        }

        return match ($config->tipo) {
            'number' => (float) $config->valor,
            'boolean' => filter_var($config->valor, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($config->valor, true),
            default => $config->valor,
        };
    }

    public static function set($chave, $valor, $tipo = 'string', $descricao = null)
    {
        $config = self::firstOrNew(['chave' => $chave]);
        
        if ($tipo === 'json') {
            $valor = json_encode($valor);
        } else {
            $valor = (string) $valor;
        }

        $config->valor = $valor;
        $config->tipo = $tipo;
        $config->descricao = $descricao;
        $config->save();

        return $config;
    }
}


