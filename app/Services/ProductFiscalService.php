<?php

namespace App\Services;

use App\Models\Produto;

class ProductFiscalService
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Preenche dados fiscais (NCM, CEST, Origem) em lote usando IA.
     *
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @param bool $force Se true, reprocessa mesmo se já tiver dados
     * @return int Número de produtos atualizados
     */
    public function fillFiscalDataBatch($products, bool $force = false): int
    {
        $count = 0;
        $productsToAi = [];
        $productsMap = [];

        foreach ($products as $produto) {
            // Proteção: Ignora serviços
            if ($produto->isService()) {
                continue;
            }

            // Se forçado OU se faltar dados, envia para IA
            if ($force || empty($produto->ncm) || empty($produto->cest)) {
                $productsToAi[] = $produto->nome;
                $productsMap[$produto->nome] = $produto;
            }
        }

        if (!empty($productsToAi)) {
            $suggestions = $this->aiService->suggestFiscalDataBatch($productsToAi);

            foreach ($suggestions as $productName => $data) {
                if (isset($productsMap[$productName])) {
                    $produto = $productsMap[$productName];
                    $updated = false;

                    // Atualiza NCM se vazio ou se forçado (e o novo valor for diferente)
                    if (!empty($data['ncm'])) {
                        $newNcm = preg_replace('/\D/', '', $data['ncm']);
                        if ($force || empty($produto->ncm)) {
                            if ($produto->ncm !== $newNcm) {
                                $produto->ncm = $newNcm;
                                $updated = true;
                            }
                        }
                    }

                    // Atualiza CEST se vazio ou se forçado
                    if (!empty($data['cest'])) {
                        $newCest = preg_replace('/\D/', '', $data['cest']);
                        if ($force || empty($produto->cest)) {
                             if ($produto->cest !== $newCest) {
                                $produto->cest = $newCest;
                                $updated = true;
                             }
                        }
                    }

                    if (isset($data['origem'])) {
                        // Origem é opcional, mas se a IA sugerir e não tivermos, podemos setar
                        if ($force || is_null($produto->origem)) {
                             if ($produto->origem !== $data['origem']) {
                                 $produto->origem = $data['origem'];
                                 $updated = true;
                             }
                        }
                    }

                    if ($updated) {
                        $produto->saveQuietly();
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Processa todos os produtos que precisam de dados fiscais.
     *
     * @param bool $force Forçar reanálise de todos os produtos
     * @return int
     */
    public function fillAll(bool $force = false): int
    {
        $query = Produto::query();

        if (!$force) {
            $query->whereNull('ncm')
                  ->orWhere('ncm', '')
                  ->orWhereNull('cest')
                  ->orWhere('cest', '');
        }

        $count = 0;
        $totalFound = $query->count();

        \Illuminate\Support\Facades\Log::info("Iniciando preenchimento fiscal em lote. Produtos encontrados para análise: {$totalFound} (Force: " . ($force ? 'SIM' : 'NÃO') . ")");
        
        if ($totalFound === 0) {
            return 0;
        }

        // Chunk de 50 para otimizar requisições (batch size da IA)
        $query->chunk(50, function ($products) use (&$count, $force) {
            $processed = $this->fillFiscalDataBatch($products, $force);
            $count += $processed;
            \Illuminate\Support\Facades\Log::info("Lote fiscal processado. Atualizados: {$processed}");
        });

        return $count;
    }
}
