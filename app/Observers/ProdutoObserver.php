<?php

namespace App\Observers;

use App\Models\Produto;
use App\Services\ProductCategorizerService;

class ProdutoObserver
{
    protected $categorizer;

    public function __construct(ProductCategorizerService $categorizer)
    {
        $this->categorizer = $categorizer;
    }

    /**
     * Handle the Produto "creating" event.
     *
     * @param  \App\Models\Produto  $produto
     * @return void
     */
    public function creating(Produto $produto)
    {
        // Tenta categorizar antes de criar.
        // Passamos false para não salvar, pois o Laravel vai salvar em seguida.
        // Passamos useAi: false para economizar requisições e deixar a IA para o processo em lote noturno.
        $this->categorizer->categorize($produto, false, false);
    }

    /**
     * Handle the Produto "updating" event.
     *
     * @param  \App\Models\Produto  $produto
     * @return void
     */
    public function updating(Produto $produto)
    {
        // Só recategoriza se o nome mudou e a categoria NÃO foi alterada manualmente nesta requisição.
        // Ou se a categoria atual é nula/padrão.
        if ($produto->isDirty('nome') && !$produto->isDirty('categoria_id')) {
             // Passamos useAi: false para economizar requisições.
             $this->categorizer->categorize($produto, false, false);
        }
    }
}
