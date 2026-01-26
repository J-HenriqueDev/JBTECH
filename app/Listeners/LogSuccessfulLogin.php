<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;

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
        $ip = $this->request->ip();
        $userAgent = $this->request->userAgent();

        // Registra o login
        Log::create([
            'user_id' => $user->id,
            'categoria' => 'Segurança',
            'acao' => 'Login Realizado',
            'detalhes' => "Login bem-sucedido para {$user->name} ({$user->email})",
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
        
        // Verificação simples de segurança (ex: IP diferente do último login)
        // Isso exigiria buscar o último log de login deste usuário
        $ultimoLogin = Log::where('user_id', $user->id)
                          ->where('acao', 'Login Realizado')
                          ->where('id', '<', DB::raw("(SELECT MAX(id) FROM logs WHERE user_id = {$user->id} AND acao = 'Login Realizado')")) // Penúltimo na verdade, pois acabamos de criar um
                          ->latest()
                          ->first();

        if ($ultimoLogin && $ultimoLogin->ip !== $ip) {
             Log::create([
                'user_id' => $user->id,
                'categoria' => 'Segurança',
                'acao' => 'Alerta de IP Novo',
                'detalhes' => "Acesso de novo IP detectado: {$ip} (Anterior: {$ultimoLogin->ip})",
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
        }
    }
}
