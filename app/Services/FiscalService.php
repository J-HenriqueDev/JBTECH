<?php

namespace App\Services;

use App\Models\Categoria;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FiscalService
{
    /**
     * Consulta dados do produto pelo código de barras (EAN/GTIN).
     * Pode ser integrado com APIs como Bluesoft Cosmos, GS1, etc.
     *
     * @param string $codigoBarras
     * @return array|null
     */
    public function consultarPorCodigoBarras($codigoBarras)
    {
        if (!$codigoBarras) {
            return null;
        }

        // 1. Tenta consulta real via API (Bluesoft Cosmos)
        try {
            $token = config('services.cosmos.token');
            if ($token) {
                $response = Http::withHeaders([
                    'X-Cosmos-Token' => $token,
                    'User-Agent' => 'Cosmos-API-Request'
                ])->get("https://api.cosmos.bluesoft.com.br/gtins/{$codigoBarras}.json");

                if ($response->successful()) {
                    $data = $response->json();

                    $nome = $data['description'] ?? null;
                    $ncm = $data['ncm']['code'] ?? null;

                    // Extrai CEST se disponível
                    $cest = $data['cest']['code'] ?? null;

                    return [
                        'nome' => $nome,
                        'ncm' => $ncm,
                        'cest' => $cest,
                        'cfop_interno' => '5102', // Padrão sugerido
                        'cfop_externo' => '6102', // Padrão sugerido
                        'origem' => 0, // Nacional
                        'unidade_comercial' => 'UN', // Padrão
                        'unidade_tributavel' => 'UN',
                        'categoria_id' => $this->sugerirCategoria($nome, $ncm),
                        // Valores fiscais padrão (devem ser conferidos pelo contador)
                        'csosn_icms' => '102',
                        'cst_icms' => '00',
                        'cst_pis' => '01',
                        'cst_cofins' => '01',
                        'aliquota_icms' => 18.00, // Padrão genérico
                        'aliquota_pis' => 1.65,
                        'aliquota_cofins' => 7.60,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro na consulta fiscal API: " . $e->getMessage());
            // Falha silenciosa para fallback
        }

        // 2. Fallback para Mock (Demonstração) se não houver token ou falhar
        // Isso permite testar a interface mesmo sem a API configurada
        $nomeSimulado = 'Produto Exemplo (Preenchido via API Mock)';
        $categoriaId = $this->sugerirCategoria($nomeSimulado, '2202.10.00');

        return [
            'nome' => $nomeSimulado,
            'ncm' => '2202.10.00',
            'cest' => '03.007.00',
            'cfop_interno' => '5102',
            'cfop_externo' => '6102',
            'origem' => 0,
            'csosn_icms' => '102',
            'cst_icms' => '00',
            'cst_pis' => '01',
            'cst_cofins' => '01',
            'aliquota_icms' => 18.00,
            'aliquota_pis' => 1.65,
            'aliquota_cofins' => 7.60,
            'unidade_comercial' => 'UN',
            'unidade_tributavel' => 'UN',
            'categoria_id' => $categoriaId
        ];
    }

    /**
     * Sugere uma categoria com base no nome do produto ou NCM.
     *
     * @param string $nomeProduto
     * @param string|null $ncm
     * @return int|null
     */
    public function sugerirCategoria($nomeProduto, $ncm = null)
    {
        $categorias = Categoria::whereNotNull('palavras_chave')->get();

        foreach ($categorias as $categoria) {
            $palavras = explode(',', $categoria->palavras_chave);
            foreach ($palavras as $palavra) {
                $palavra = trim($palavra);
                if (empty($palavra)) continue;

                // Verifica se a palavra chave está contida no nome do produto (case insensitive)
                if (Str::contains(Str::lower($nomeProduto), Str::lower($palavra))) {
                    return $categoria->id;
                }

                // Se o NCM for passado, verifica se a palavra chave é o NCM
                if ($ncm && $palavra == $ncm) {
                    return $categoria->id;
                }
            }
        }

        return null;
    }

    /**
     * Busca estimativa de impostos (IBPT).
     *
     * @param string $ncm
     * @param string $uf
     * @return array
     */
    /*
    public function buscarImpostosIbpt($ncm, $uf)
    {
        // Método desativado/removido conforme solicitação
        return [];
    }
    */
}
