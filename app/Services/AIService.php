<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-001:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function suggestCategory(string $productName): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $prompt = "Classifique o produto '{$productName}' em uma única categoria curta e geral para um sistema de gestão de loja de informática (ex: 'Periféricos', 'Hardware', 'Redes', 'Serviços'). Retorne apenas o nome da categoria, sem pontuação final.";

            return $this->generateContent($prompt);
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
        }

        return null;
    }

    public function suggestCategoriesBatch(array $productNames): array
    {
        if (empty($this->apiKey) || empty($productNames)) {
            return [];
        }

        // Limita o lote para evitar estourar tokens (aprox 50 produtos por vez é seguro)
        $productsList = implode("\n", $productNames);

        $prompt = "Atue como um especialista em hardware e informática. Classifique a lista de produtos abaixo em categorias curtas e padronizadas (ex: 'Processadores', 'Placas de Vídeo', 'Armazenamento', 'Periféricos', 'Redes', 'Cabos', 'Impressão').

        Regras:
        1. Retorne APENAS um JSON válido.
        2. O formato deve ser chave-valor: {\"Nome do Produto\": \"Categoria\"}.
        3. Não use Markdown (```json).
        4. Se não souber, use 'Outros'.

        Lista de Produtos:
        {$productsList}";

        try {
            $response = $this->generateContent($prompt);

            if ($response) {
                // Limpa possíveis blocos de código markdown que a IA possa adicionar
                $cleanJson = str_replace(['```json', '```'], '', $response);
                $data = json_decode($cleanJson, true);

                return is_array($data) ? $data : [];
            }
        } catch (\Exception $e) {
            Log::error('Gemini Batch Service Exception: ' . $e->getMessage());
        }

        return [];
    }

    public function suggestFiscalDataBatch(array $productNames): array
    {
        if (empty($this->apiKey) || empty($productNames)) {
            return [];
        }

        $productsList = implode("\n", $productNames);

        $prompt = "Atue como um contador especialista em hardware e tributação brasileira. Para a lista de produtos abaixo, forneça o NCM (8 dígitos) e o CEST (se aplicável, 7 dígitos) mais prováveis.

        Regras:
        1. Retorne APENAS um JSON válido.
        2. Formato: {\"Nome do Produto\": {\"ncm\": \"00000000\", \"cest\": \"0000000\", \"origem\": 0}}.
        3. Se não tiver certeza, deixe 'null'.
        4. Origem: 0 para Nacional, 1 ou 2 para Importado (assuma 0 se dúvida).

        Lista de Produtos:
        {$productsList}";

        try {
            $response = $this->generateContent($prompt);

            if ($response) {
                $cleanJson = str_replace(['```json', '```'], '', $response);
                $data = json_decode($cleanJson, true);

                return is_array($data) ? $data : [];
            }
        } catch (\Exception $e) {
            Log::error('Gemini Fiscal Batch Exception: ' . $e->getMessage());
        }

        return [];
    }

    public function explainNfeError(string $errorCode, string $errorMessage, ?string $xmlContext = null): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $prompt = "Atue como um especialista em NFe e SEFAZ. Explique de forma simples e direta como corrigir o seguinte erro de emissão:

        Código: {$errorCode}
        Mensagem: {$errorMessage}
        " . ($xmlContext ? "Contexto XML (trecho): {$xmlContext}" : "") . "

        Responda em tópicos curtos:
        1. O que aconteceu (Explicação humana)
        2. Como corrigir (Passo a passo prático para o usuário do sistema)
        ";

        return $this->generateContent($prompt);
    }

    public function improveProductDescription(string $productName): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $prompt = "Melhore a descrição deste produto de informática para um orçamento comercial. Torne-a mais atraente, legível e profissional, removendo códigos técnicos desnecessários, mas mantendo especificações importantes (GB, GHz, Modelo).

        Produto Original: {$productName}

        Retorne apenas a nova descrição, sem aspas.";

        return $this->generateContent($prompt);
    }

    protected function generateContent(string $prompt): ?string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($text) {
                // Se o prompt pede JSON, não devemos remover aspas indiscriminadamente
                if (str_contains($prompt, 'JSON')) {
                    return $text;
                }
                return trim(str_replace(['"', "'"], '', $text));
            }
        }

        Log::error('Gemini API Error: ' . $response->body());
        return null;
    }
}
