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
                    $mensagem = "[{$userName}] - Alterou {$key} de '{$oldValue}' para '{$newValue}' em " . class_basename($model) . " #{$model->id}";
                    if (method_exists(LogService::class, 'cagueta')) {
                        LogService::cagueta($mensagem);
                    }
                }
            }
        });
    }
}
