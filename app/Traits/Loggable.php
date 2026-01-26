<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Services\LogService;

trait Loggable
{
    public static function bootLoggable()
    {
        static::updated(function ($model) {
            $user = Auth::user();
            $userName = $user ? $user->name : 'Sistema/Desconhecido';
            $origem = $user ? 'Humano' : 'Sistema';

            // Campos para ignorar (timestamps, etc)
            $ignoredColumns = ['updated_at', 'created_at', 'remember_token', 'password'];

            foreach ($model->getDirty() as $key => $newValue) {
                if (in_array($key, $ignoredColumns)) {
                    continue;
                }

                $oldValue = $model->getOriginal($key);

                // Evita logar se a mudança for apenas de tipo mas valor igual (ex: "10.00" vs 10.00)
                if ($oldValue == $newValue) {
                    continue;
                }

                $acao = "Editou " . class_basename($model) . " {$model->id}";
                $detalhes = "Campo '{$key}' alterado de '{$oldValue}' para '{$newValue}'";

                // Usa o LogService existente para manter consistência
                // Se for sistema (CLI/Job), origem é Sistema. Se tiver user logado, Humano.
                // Mas o LogService::registrar espera (categoria, acao, detalhes).
                // O LogService::registrarMudanca criado anteriormente é perfeito aqui.

                // Tenta identificar um nome amigável para o registro
                $nomeItem = $model->nome ?? $model->titulo ?? $model->name ?? $model->descricao ?? null;

                if (method_exists(LogService::class, 'registrarMudanca')) {
                    LogService::registrarMudanca(class_basename($model), $model->id, $key, $oldValue, $newValue, $nomeItem);
                } else {
                    // Fallback se o método não existir (embora deva existir pelo passo anterior)
                    LogService::registrar($origem, $acao, $detalhes);
                }
            }
        });

        // Opcional: Logar criação e deleção se necessário no futuro
        // static::created(...)
        // static::deleted(...)
    }
}
