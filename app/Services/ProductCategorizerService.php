<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Support\Str;

class ProductCategorizerService
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Categoriza um produto automaticamente com base em palavras-chave ou IA.
     *
     * @param Produto $produto
     * @param bool $save Se deve salvar o produto após a alteração.
     * @param bool $useAi Se deve utilizar IA caso a categorização por palavras-chave falhe.
     * @return bool Retorna true se a categoria foi alterada, false caso contrário.
     */
    public function categorize(Produto $produto, bool $save = true, bool $useAi = true): bool
    {
        // Proteção: Nunca alterar produtos de serviço
        if ($produto->isService()) {
            return false;
        }

        // 1. Tenta por Palavras-Chave (Mais rápido e barato)
        if ($this->categorizeByKeywords($produto, $save)) {
            return true;
        }

        // 2. Se não encontrou e tem permissão para IA, tenta por IA
        if ($useAi && $this->categorizeByAI($produto, $save)) {
            return true;
        }

        return false;
    }

    protected function categorizeByKeywords(Produto $produto, bool $save): bool
    {
        $categorias = Categoria::whereNotNull('palavras_chave')
            ->where('palavras_chave', '!=', '')
            ->get();

        $productName = Str::lower($produto->nome);
        $bestMatch = null;
        $maxScore = 0;

        foreach ($categorias as $categoria) {
            $keywords = explode(',', Str::lower($categoria->palavras_chave));

            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) continue;

                if (Str::contains($productName, $keyword)) {
                    $score = strlen($keyword);
                    if ($productName === $keyword) $score += 10;
                    elseif (Str::startsWith($productName, $keyword)) $score += 5;

                    if ($score > $maxScore) {
                        $maxScore = $score;
                        $bestMatch = $categoria;
                    }
                }
            }
        }

        if ($bestMatch && $bestMatch->id !== $produto->categoria_id) {
            $this->applyCategory($produto, $bestMatch->id, $save);
            return true;
        }

        return false;
    }

    protected function categorizeByAI(Produto $produto, bool $save): bool
    {
        $suggestedName = $this->aiService->suggestCategory($produto->nome);

        if (!$suggestedName) {
            return false;
        }

        // Verifica se já existe uma categoria com esse nome (ou muito parecido)
        $categoria = Categoria::where('nome', 'LIKE', $suggestedName)->first();

        if (!$categoria) {
            // Cria a nova categoria
            $categoria = Categoria::create([
                'nome' => Str::title($suggestedName),
                'palavras_chave' => null // Podemos pedir pra IA gerar palavras-chave futuramente
            ]);
        }

        if ($categoria->id !== $produto->categoria_id) {
            $this->applyCategory($produto, $categoria->id, $save);
            return true;
        }

        return false;
    }

    protected function applyCategory(Produto $produto, int $categoryId, bool $save): void
    {
        $produto->categoria_id = $categoryId;
        if ($save) {
            $produto->saveQuietly();
        }
    }

    /**
     * Categoriza uma coleção de produtos em lote, otimizando requisições.
     *
     * @param \Illuminate\Database\Eloquent\Collection $products
     * @return int Número de produtos atualizados
     */
    public function categorizeBatch($products): int
    {
        $count = 0;
        $productsToAi = [];
        $productsMap = [];

        // 1. Tenta categorizar localmente primeiro (grátis e rápido)
        foreach ($products as $produto) {
            // Proteção: Ignora serviços
            if ($produto->isService()) {
                continue;
            }

            if ($this->categorizeByKeywords($produto, true)) {
                $count++;
            } else {
                // Se não conseguiu por palavra-chave, adiciona à lista para IA
                $productsToAi[] = $produto->nome;
                $productsMap[$produto->nome] = $produto;
            }
        }

        // 2. Envia os restantes para a IA em lote
        if (!empty($productsToAi)) {
            $suggestions = $this->aiService->suggestCategoriesBatch($productsToAi);

            foreach ($suggestions as $productName => $categoryName) {
                if (isset($productsMap[$productName])) {
                    $produto = $productsMap[$productName];

                    // Lógica de criação/busca de categoria (igual ao single mode)
                    $categoryName = Str::title($categoryName);
                    $categoria = Categoria::firstOrCreate(
                        ['nome' => $categoryName],
                        ['palavras_chave' => null]
                    );

                    if ($categoria->id !== $produto->categoria_id) {
                        $this->applyCategory($produto, $categoria->id, true);
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Categoriza todos os produtos que não possuem categoria ou todos se forçado.
     *
     * @param bool $force Recategorizar todos os produtos mesmo os que já têm categoria.
     * @return int Número de produtos atualizados.
     */
    public function categorizeAll(bool $force = false): int
    {
        $query = Produto::query();

        if (!$force) {
            $query->whereNull('categoria_id')
                  ->orWhere('categoria_id', 1)
                  ->orWhere('categoria_id', 6); // Inclui categoria padrão 'Geral'
        }

        $totalFound = $query->count();
        \Illuminate\Support\Facades\Log::info("Iniciando categorização em lote. Produtos encontrados: {$totalFound}");
        echo "Produtos encontrados para análise: {$totalFound}\n";

        if ($totalFound === 0) {
            return 0;
        }

        // Processa em chunks de 50 para não sobrecarregar a memória e otimizar o lote da IA
        $count = 0;
        $query->chunk(50, function ($products) use (&$count) {
            $processed = $this->categorizeBatch($products);
            $count += $processed;
            echo "Lote processado. Atualizados: {$processed}\n";
        });

        return $count;
    }
}
