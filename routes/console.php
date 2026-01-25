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
// Roda a cada 10 minutos para garantir agilidade
Schedule::command('nfe:processar-destinadas')->everyTenMinutes();

// Despacho de notificações agendadas (lembretes)
Schedule::command('notifications:dispatch')->everyFiveMinutes();

// Processamento automático de contratos (gera cobranças e NFS-e)
// Roda diariamente às 08:00
Schedule::command('contratos:processar')->dailyAt('08:00');
