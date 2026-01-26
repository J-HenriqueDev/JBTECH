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
     * @return int Número de produtos atualizados
     */
    public function fillFiscalDataBatch($products): int
    {
        $count = 0;
        $productsToAi = [];
        $productsMap = [];

        foreach ($products as $produto) {
            // Proteção: Ignora serviços
            if ($produto->isService()) {
                continue;
            }

            // Só envia para IA se faltar NCM ou CEST (assumindo que NCM é o mais crítico)
            if (empty($produto->ncm) || empty($produto->cest)) {
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

                    if (empty($produto->ncm) && !empty($data['ncm'])) {
                        $produto->ncm = preg_replace('/\D/', '', $data['ncm']); // Remove pontuação
                        $updated = true;
                    }

                    if (empty($produto->cest) && !empty($data['cest'])) {
                        $produto->cest = preg_replace('/\D/', '', $data['cest']);
                        $updated = true;
                    }

                    if (isset($data['origem'])) {
                        // Origem é opcional, mas se a IA sugerir e não tivermos, podemos setar
                        // Mas cuidado para não sobrescrever se já existir.
                        // Assumindo que origem padrão é null ou 0.
                        // Vamos ser conservadores e só atualizar se for explicitamente sugerido e estiver null.
                        if (is_null($produto->origem)) {
                             $produto->origem = $data['origem'];
                             $updated = true;
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
        // Chunk de 50 para otimizar requisições (batch size da IA)
        $query->chunk(50, function ($products) use (&$count) {
            $count += $this->fillFiscalDataBatch($products);
        });

        return $count;
    }
}
