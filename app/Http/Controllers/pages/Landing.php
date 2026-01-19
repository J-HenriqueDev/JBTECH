<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Review; // Importando o modelo Review
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Importando a classe Log

class Landing extends Controller
{
    public function index()
    {
        // Definir locale para Carbon
        \Carbon\Carbon::setLocale('pt_BR');

        // Consultando as avaliações
        $reviews = Review::all();

        // Registrando no log quando as avaliações são consultadas
        Log::info('Avaliações consultadas na landing page.', [
            'count' => $reviews->count(),
            'timestamp' => now(),
        ]);

        // Buscando notícias via RSS (TecMundo)
        $news = [];
        try {
            // Contexto para timeout de 2 segundos para não travar o carregamento
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2
                ]
            ]);

            // Google News RSS para Automação Comercial no Brasil
            $rss_url = 'https://news.google.com/rss/search?q=automação+comercial+brasil+varejo+tecnologia&hl=pt-BR&gl=BR&ceid=BR:pt-419';
            $rss_content = @file_get_contents($rss_url, false, $context);

            if ($rss_content) {
                $rss = simplexml_load_string($rss_content);
                if ($rss) {
                    $count = 0;
                    foreach ($rss->channel->item as $item) {
                        if ($count >= 4) break;

                        // Google News não fornece imagens facilmente no RSS padrão,
                        // então usaremos ícones baseados em palavras-chave ou padrão
                        $description = strip_tags((string)$item->description);
                        // Limpar descrição do Google News que vem com links e fontes
                        $description = preg_replace('/<a href=".+">.+<\/a>/', '', $description);
                        $description = preg_replace('/&nbsp;/', '', $description);

                        $news[] = [
                            'title' => (string)$item->title,
                            'link' => (string)$item->link,
                            'description' => \Illuminate\Support\Str::limit($description, 100),
                            'date' => date('d/m/Y', strtotime((string)$item->pubDate)),
                            'image' => '' // Sem imagem por enquanto
                        ];
                        $count++;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao buscar notícias RSS: ' . $e->getMessage());
        }

        $pageConfigs = ['myLayout' => 'front'];
        return view('content.pages.landing-page', [
            'pageConfigs' => $pageConfigs,
            'reviews' => $reviews, // Passando as avaliações para a view
            'news' => $news // Passando as notícias
        ]);
    }
}
