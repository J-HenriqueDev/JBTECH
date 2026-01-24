<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Comando inspirador
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Comando para buscar avaliações do Google
Schedule::command('fetch:google-reviews')->weekly(); // ou daily() se preferir

// Comando para processar NFe destinadas (Consulta e Download automático)
// Roda a cada hora para respeitar limites da SEFAZ e evitar bloqueios
Schedule::command('nfe:processar-destinadas')->hourly();

// Despacho de notificações agendadas (lembretes)
Schedule::command('notifications:dispatch')->everyFiveMinutes();

// Processamento automático de contratos (gera cobranças e NFS-e)
// Roda diariamente às 08:00
Schedule::command('contratos:processar')->dailyAt('08:00');
