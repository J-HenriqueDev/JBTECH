<?php

namespace App\Services;

use App\Models\NotaEntrada;
use App\Models\Produto;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Importa itens da nota fiscal de entrada para o estoque.
     * Incrementa o estoque se o produto for encontrado pelo EAN ou Código Interno.
     *
     * @param NotaEntrada $nota
     * @return array
     */
    public function importarItensDaNota(NotaEntrada $nota)
    {
        if (empty($nota->xml_content)) {
            Log::warning("InventoryService: Nota #{$nota->id} sem XML content.");
            return ['status' => 'error', 'message' => 'XML não encontrado'];
        }

        try {
            $xml = simplexml_load_string($nota->xml_content);
            if (!$xml) {
                throw new \Exception("Falha ao ler XML da nota #{$nota->id}");
            }

            // Namespace padrão da NFe
            $ns = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('nfe', $ns[''] ?? 'http://www.portalfiscal.inf.br/nfe');

            // Se for procNFe, o XML da nota está dentro. Se for NFe direto, usa a raiz.
            if (isset($xml->NFe)) {
                $infNFe = $xml->NFe->infNFe;
            } else {
                $infNFe = $xml->infNFe;
            }

            if (!$infNFe) {
                // Tenta encontrar via XPath caso a estrutura seja diferente
                $infNFe = $xml->xpath('//nfe:infNFe')[0] ?? null;
            }

            if (!$infNFe) {
                throw new \Exception("Estrutura infNFe não encontrada no XML.");
            }

            $itensProcessados = 0;
            $itensAtualizados = 0;

            foreach ($infNFe->det as $item) {
                $itensProcessados++;
                $prod = $item->prod;
                
                $ean = (string) $prod->cEAN;
                $codigoInterno = (string) $prod->cProd;
                $quantidade = (float) $prod->qCom;
                $nomeProdutoXml = (string) $prod->xProd;

                // Tenta encontrar produto
                $produto = null;

                // 1. Busca por Código Interno (cProd) do fornecedor (prioridade do usuário)
                if (!empty($codigoInterno)) {
                    Log::info("Tentando encontrar produto pelo código interno (cProd): {$codigoInterno}");
                    $produto = Produto::where('codigo_barras', $codigoInterno)->first();
                    if ($produto) {
                        Log::info("Produto encontrado pelo código interno (cProd): {$codigoInterno} - ID: {$produto->id}");
                    }
                }

                // 2. Busca por EAN (se válido e não for "SEM GTIN") - fallback
                if (!$produto && !empty($ean) && $ean !== 'SEM GTIN') {
                    Log::info("Tentando encontrar produto pelo EAN: {$ean} (fallback)");
                    $produto = Produto::where('codigo_barras', $ean)->first();
                    if ($produto) {
                        Log::info("Produto encontrado pelo EAN: {$ean} - ID: {$produto->id}");
                    }
                }

                if ($produto) {
                    // Incrementa estoque
                    $estoqueAnterior = $produto->estoque;
                    $produto->estoque += $quantidade;
                    $produto->save();
                    
                    $itensAtualizados++;
                    Log::info("InventoryService: Produto atualizado. ID: {$produto->id}, XML: {$nomeProdutoXml}, Qtd: +{$quantidade}, Estoque: {$estoqueAnterior} -> {$produto->estoque}");
                } else {
                    Log::info("InventoryService: Produto não encontrado para EAN: {$ean} ou Código: {$codigoInterno} ({$nomeProdutoXml})");
                }
            }

            return [
                'status' => 'success',
                'message' => "Processamento concluído. {$itensAtualizados} de {$itensProcessados} itens atualizados no estoque."
            ];

        } catch (\Exception $e) {
            Log::error("InventoryService: Erro ao importar itens: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
