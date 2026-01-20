<?php

namespace App\Services;

use App\Models\Categoria;
use Illuminate\Support\Str;

class CategorizerService
{
    /**
     * Sugere uma categoria baseada no nome do produto e palavras-chave.
     *
     * @param string $nomeProduto
     * @return int ID da categoria sugerida
     */
    public function sugerirCategoria(string $nomeProduto): int
    {
        // Normaliza o nome do produto para comparação
        $nomeNormalizado = Str::slug($nomeProduto, ' ');
        
        // Busca todas as categorias com suas palavras-chave
        // Cachear isso seria ideal em produção, mas para agora faremos direto
        $categorias = Categoria::whereNotNull('palavras_chave')->get();
        
        $melhorCategoriaId = null;
        $maiorPontuacao = 0;

        foreach ($categorias as $categoria) {
            $pontuacao = 0;
            $keywords = explode(',', $categoria->palavras_chave);
            
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) continue;
                
                $keywordNormalizada = Str::slug($keyword, ' ');
                
                // Verifica se a palavra-chave existe no nome do produto
                if (Str::contains($nomeNormalizado, $keywordNormalizada)) {
                    // Pontuação baseada no tamanho da palavra-chave (preferência por termos mais específicos)
                    $pontuacao += strlen($keywordNormalizada);
                    
                    // Bônus se for uma correspondência exata de palavra (limitada por espaços)
                    if (preg_match('/\b' . preg_quote($keywordNormalizada, '/') . '\b/', $nomeNormalizado)) {
                        $pontuacao += 5;
                    }
                }
            }
            
            if ($pontuacao > $maiorPontuacao) {
                $maiorPontuacao = $pontuacao;
                $melhorCategoriaId = $categoria->id;
            }
        }

        // Se encontrou uma correspondência, retorna o ID
        if ($melhorCategoriaId) {
            return $melhorCategoriaId;
        }

        // Se não encontrou, tenta retornar a categoria "Outros" ou a primeira disponível (fallback)
        $outros = Categoria::where('nome', 'Outros')->first();
        if ($outros) {
            return $outros->id;
        }

        return Categoria::first()->id ?? 1;
    }
}
