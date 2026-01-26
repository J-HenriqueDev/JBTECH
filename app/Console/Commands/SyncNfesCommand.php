<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NfeDownloadService;
use Illuminate\Support\Facades\Log;

class SyncNfesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nfe:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza NF-e destinadas via DistDFe';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronização de NF-e...');
        Log::info('Command nfe:sync iniciado.');

        try {
            $service = new NfeDownloadService();
            $service->executarSincronizacao();
            $this->info('Sincronização finalizada.');
        } catch (\Exception $e) {
            $this->error('Erro durante a sincronização: ' . $e->getMessage());
            Log::error('Command nfe:sync erro: ' . $e->getMessage());
        }
    }
}
