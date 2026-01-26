<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Services\LogService;

trait Loggable
{
    public static function bootLoggable()
    {
        static::updating(function ($model) {
            $user = Auth::user();
            $userName = $user ? $user->name : 'Sistema/Desconhecido';

            $ignoredColumns = ['updated_at', 'created_at', 'remember_token', 'password'];

            foreach ($model->getDirty() as $key => $newValue) {
                if (in_array($key, $ignoredColumns)) {
                    continue;
                }

                $oldValue = $model->getOriginal($key);

                if ($oldValue == $newValue) {
                    continue;
                }

                $nomeItem = $model->nome ?? $model->titulo ?? $model->name ?? $model->descricao ?? null;

                if (method_exists(LogService::class, 'registrarMudanca')) {
                    LogService::registrarMudanca(class_basename($model), $model->id, $key, $oldValue, $newValue, $nomeItem);
                } else {
                    $detalhes = "[{$userName}] - Alterou {$key} de '{$oldValue}' para '{$newValue}'";
                    if (method_exists(LogService::class, 'registrar')) {
                        LogService::registrar('Auditoria', "Edição de " . class_basename($model), $detalhes);
                    }
                }
            }
        });

        static::deleted(function ($model) {
            $user = Auth::user();
            $userName = $user ? $user->name : 'Sistema/Desconhecido';
            $nomeItem = $model->nome ?? $model->titulo ?? $model->name ?? $model->descricao ?? null;

            $detalhes = "[Humano: {$userName}] - Deletou o item: {$nomeItem} (ID: {$model->id})";

            if (method_exists(LogService::class, 'registrar')) {
                LogService::registrar('Auditoria', "Exclusão de " . class_basename($model), $detalhes);
            }
        });
    }
}
