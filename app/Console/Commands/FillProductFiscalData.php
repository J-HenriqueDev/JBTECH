<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductFiscalService;

class FillProductFiscalData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fill-fiscal {--force : Forçar atualização de todos os produtos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preenche dados fiscais (NCM, CEST) dos produtos usando IA em lote.';

    protected $fiscalService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProductFiscalService $fiscalService)
    {
        parent::__construct();
        $this->fiscalService = $fiscalService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando preenchimento de dados fiscais...');

        $force = $this->option('force');
        
        if ($force) {
            $this->warn('Modo FORCE ativado: Todos os produtos serão reavaliados.');
        }

        $count = $this->fiscalService->fillAll($force);

        $this->info("Processo concluído. {$count} produtos foram atualizados com dados fiscais.");

        return 0;
    }
}
