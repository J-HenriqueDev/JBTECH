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
        $ip = $this->request->ip();
        $userAgent = $this->request->userAgent();

        $ultimoLogin = Log::where('user_id', $user->id)
            ->whereIn('acao', ['Login Realizado', 'Login Habitual', 'Login Não Habitual'])
            ->latest()
            ->first();

        $habitual = $ultimoLogin && $ultimoLogin->ip === $ip;
        $acao = $habitual ? 'Login Habitual' : 'Login Não Habitual';

        Log::create([
            'user_id' => $user->id,
            'categoria' => 'Segurança',
            'acao' => $acao,
            'detalhes' => "Login para {$user->name} ({$user->email}) a partir do IP {$ip}",
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
