<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductCategorizerService;

class CategorizeProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:categorize {--force : Forçar recategorização de todos os produtos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Categoriza produtos automaticamente com base em palavras-chave definidas nas categorias.';

    protected $categorizer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ProductCategorizerService $categorizer)
    {
        parent::__construct();
        $this->categorizer = $categorizer;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando categorização automática de produtos...');

        $force = $this->option('force');
        
        if ($force) {
            $this->warn('Modo FORCE ativado: Todos os produtos serão reavaliados.');
        }

        $count = $this->categorizer->categorizeAll($force);

        $this->info("Processo concluído. {$count} produtos foram atualizados.");

        return 0;
    }
}
