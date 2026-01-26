<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaEntrada extends Model
{
    use HasFactory;

    protected $table = 'notas_entradas';

    protected $fillable = [
        'chave_acesso',
        'nsu',
        'numero_nfe',
        'serie',
        'emitente_cnpj',
        'emitente_nome',
        'valor_total',
        'data_emissao',
        'status',
        'xml_content',
        'manifestacao',
        'user_id'
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'valor_total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna os itens formatados do XML para exibição na view.
     * Inclui link com produto interno se encontrado.
     */
    public function getItensFormatadosAttribute()
    {
        if (empty($this->xml_content)) {
            return [];
        }

        try {
            $xml = simplexml_load_string($this->xml_content);
            if (!$xml) return [];

            // Namespaces
            $ns = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('nfe', $ns[''] ?? 'http://www.portalfiscal.inf.br/nfe');

            // Tenta localizar infNFe com ou sem namespace
            $infNFe = $xml->xpath('//nfe:infNFe')[0] ?? $xml->infNFe ?? $xml->NFe->infNFe ?? null;

            if (!$infNFe) return [];

            $emitenteNome = (string) ($infNFe->emit->xNome ?? '');
            $itens = [];

            foreach ($infNFe->det as $det) {
                $prod = $det->prod;
                $ean = (string) $prod->cEAN;
                $qtd = (float) $prod->qCom;
                $valor = (float) $prod->vProd;
                $nomeItem = (string) $prod->xProd;

                $itemData = [
                    'nome_fornecedor' => $emitenteNome,
                    'ean' => $ean,
                    'quantidade' => $qtd,
                    'valor' => $valor,
                    'nome_item' => $nomeItem,
                    'produto_id' => null,
                    'produto_nome_interno' => null
                ];

                // Dedo Duro: Verifica se existe no banco
                if (!empty($ean) && $ean !== 'SEM GTIN') {
                    $produto = \App\Models\Produto::where('codigo_barras', $ean)->first();
                    if ($produto) {
                        $itemData['produto_id'] = $produto->id;
                        $itemData['produto_nome_interno'] = $produto->nome;
                    }
                }

                $itens[] = $itemData;
            }

            return $itens;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao formatar itens da nota {$this->chave_acesso}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tenta associar os itens do XML aos produtos do banco para auxiliar na conferência.
     * Retorna uma lista de itens do XML com sugestões de produtos do banco.
     */
    public function sugerirItens()
    {
        if (empty($this->xml_content)) {
            return [];
        }

        try {
            $xml = simplexml_load_string($this->xml_content);
            if (!$xml) return [];

            $ns = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('nfe', $ns[''] ?? 'http://www.portalfiscal.inf.br/nfe');

            if (isset($xml->NFe)) {
                $infNFe = $xml->NFe->infNFe;
            } else {
                $infNFe = $xml->infNFe;
            }

            if (!$infNFe) {
                $infNFe = $xml->xpath('//nfe:infNFe')[0] ?? null;
            }

            if (!$infNFe) return [];

            $sugestoes = [];

            foreach ($infNFe->det as $item) {
                $prod = $item->prod;
                $ean = (string) $prod->cEAN;
                $codigoInterno = (string) $prod->cProd;
                $nome = (string) $prod->xProd;
                $qtd = (float) $prod->qCom;
                $valor = (float) $prod->vUnCom;

                $produtoSugerido = null;

                // 1. Busca por EAN
                if (!empty($ean) && $ean !== 'SEM GTIN') {
                    $produtoSugerido = \App\Models\Produto::where('codigo_barras', $ean)->first();
                }

                // 2. Busca por Código Interno
                if (!$produtoSugerido && !empty($codigoInterno)) {
                    // Assume que o código do fornecedor pode estar no campo codigo_barras ou id?
                    // Seguindo a lógica do InventoryService:
                    $produtoSugerido = \App\Models\Produto::where('codigo_barras', $codigoInterno)->first();
                }

                $sugestoes[] = [
                    'xml_nome' => $nome,
                    'xml_ean' => $ean,
                    'xml_codigo' => $codigoInterno,
                    'xml_qtd' => $qtd,
                    'xml_valor' => $valor,
                    'produto_sugerido' => $produtoSugerido
                ];
            }

            return $sugestoes;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao gerar sugestões para NotaEntrada #{$this->id}: " . $e->getMessage());
            return [];
        }
    }
}
