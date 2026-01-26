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

                // 1. Busca por EAN (se válido e não for "SEM GTIN")
                if (!empty($ean) && $ean !== 'SEM GTIN') {
                    $produto = Produto::where('codigo_barras', $ean)->first();
                }

                // 2. Busca por Código Interno (cProd) se não achou por EAN
                // Assumimos que o cProd do fornecedor pode bater com nosso codigo_barras ou ID, 
                // mas a instrução pede para buscar pelo "Código Interno (prod->cProd)".
                // Vamos assumir que mapeia para 'codigo_barras' ou algum campo de código do sistema.
                // Como o modelo Produto tem 'codigo_barras', vamos tentar bater lá também, ou no ID se for numérico?
                // O usuário disse: "procurar o produto no banco JBTECH pelo EAN (prod->cEAN) ou pelo Código Interno (prod->cProd)."
                // Vou buscar prod->cProd no campo 'codigo_barras' também, ou talvez em 'id' se for match exato.
                // Para segurança, vamos buscar em 'codigo_barras' primeiro.
                
                if (!$produto && !empty($codigoInterno)) {
                    $produto = Produto::where('codigo_barras', $codigoInterno)->first();
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
