<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Configuracao;
use App\Models\User;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use App\Services\LogService;

class DispatchScheduledNotifications extends Command
{
    protected $signature = 'notifications:dispatch';
    protected $description = 'Despacha notificações agendadas armazenadas em Configuracao';

    public function handle()
    {
        $scheduledJson = Configuracao::get('scheduled_notifications');
        if (!$scheduledJson) {
            $this->info('Nenhuma notificação agendada.');
            return Command::SUCCESS;
        }
        try {
            $list = json_decode($scheduledJson, true) ?: [];
        } catch (\Exception $e) {
            $this->error('JSON inválido em scheduled_notifications');
            return Command::FAILURE;
        }

        $now = Carbon::now();
        $changed = false;
        foreach ($list as &$item) {
            if (!empty($item['sent_at'])) {
                continue;
            }
            $when = Carbon::parse($item['scheduled_at'] ?? $now);
            if ($when->lte($now)) {
                $targets = collect();
                if (($item['target'] ?? 'all') === 'all') {
                    $targets = User::all();
                } elseif (($item['target'] ?? 'all') === 'role') {
                    $targets = User::where('role', $item['role'])->get();
                } else {
                    if (!empty($item['user_id'])) {
                        $u = User::find($item['user_id']);
                        if ($u) $targets = collect([$u]);
                    }
                }
                $notification = new SystemNotification(
                    $item['title'] ?? 'Notificação',
                    $item['message'] ?? '',
                    $item['type'] ?? 'info',
                    $item['link'] ?? null
                );
                foreach ($targets as $u) {
                    $u->notify($notification);
                }
                $item['sent_at'] = Carbon::now()->toDateTimeString();
                LogService::registrar('Notificação', 'Despachar', "Título: " . ($item['title'] ?? 'Notificação') . " | Destino: " . ($item['target'] ?? 'all') . ($item['target'] === 'user' ? " | User ID: " . ($item['user_id'] ?? '') : ($item['target'] === 'role' ? " | Role: " . ($item['role'] ?? '') : "")));
                $changed = true;
            }
        }
        unset($item);

        if ($changed) {
            Configuracao::set('scheduled_notifications', json_encode($list), 'notifications', 'json', 'Notificações agendadas');
            $this->info('Notificações agendadas despachadas.');
        } else {
            $this->info('Nada para despachar.');
        }

        return Command::SUCCESS;
    }
}
