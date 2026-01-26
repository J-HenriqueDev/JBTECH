<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use App\Models\Log;

class LogSuccessfulLogin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(Login $event)
    {
        $user = $event->user;
        $ip = request()->ip(); // Pega IP atual de forma segura

        // Busca último login deste usuário para comparar IP
        // Consideramos qualquer ação de Login anterior na categoria Segurança
        $ultimoLogin = Log::where('user_id', $user->id)
            ->where('categoria', 'Segurança')
            ->where('acao', 'Login')
            ->orderBy('id', 'desc')
            ->first();

        $emoji = '';
        // Se houver login anterior e o IP for diferente, adiciona alerta
        if ($ultimoLogin && $ultimoLogin->ip !== $ip) {
            $emoji = '⚠️ ';
        }

        $detalhes = "{$emoji}Login realizado com sucesso. IP: {$ip}";

        // Usa o LogService para manter consistência (assume que Auth::user() já está disponível)
        // Se LogService não estiver disponível via facade, usamos Log::create direto como fallback implícito no LogService
        \App\Services\LogService::registrar('Segurança', 'Login', $detalhes);
    }
}
