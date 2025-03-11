<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Review;
use Carbon\Carbon;

class FetchGoogleReviews extends Command
{
    protected $signature = 'fetch:google-reviews';
    protected $description = 'Fetch Google Reviews and store them';

    public function handle()
    {
        $apiKey = env('GOOGLE_API_KEY');
        $placeId = env('GOOGLE_PLACE_ID');

        // Verifica se a API Key e o Place ID estão configurados
        if (empty($apiKey) || empty($placeId)) {
            $this->error('API Key ou Place ID não configurados.');
            return;
        }

        // Buscar avaliações por relevância e mais recentes
        $sortOrders = ['most_relevant', 'newest'];

        foreach ($sortOrders as $sortOrder) {
            $this->fetchReviews($apiKey, $placeId, $sortOrder);
        }
    }

    private function fetchReviews($apiKey, $placeId, $sortOrder)
    {
        try {
            $nextPageToken = null;
            $requestCount = 0;
            $maxRequests = 10; // Limite de requisições para evitar sobrecarga

            do {
                // Monta a URL da API
                $url = "https://maps.googleapis.com/maps/api/place/details/json";
                $queryParams = [
                    'place_id' => $placeId,
                    'key' => $apiKey,
                    'fields' => 'reviews',
                    'reviews_sort' => $sortOrder,
                ];

                // Adiciona o token de paginação, se houver
                if ($nextPageToken) {
                    $queryParams['pagetoken'] = $nextPageToken;
                }

                // Faz a requisição à API
                $response = Http::get($url, $queryParams);

                // Verifica se a requisição foi bem-sucedida
                if (!$response->successful()) {
                    $this->error('Erro na requisição à API do Google.');
                    Log::error('Erro na requisição à API do Google.', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    return;
                }

                $data = $response->json();

                // Verifica se há erros na resposta da API
                if (isset($data['error_message'])) {
                    $this->error('Erro na API do Google: ' . $data['error_message']);
                    Log::error('Erro na API do Google.', ['error' => $data['error_message']]);
                    return;
                }

                // Verifica se o campo 'reviews' está presente e não está vazio
                if (empty($data['result']['reviews'])) {
                    $this->warn('Nenhuma avaliação encontrada para o local com a ordem: ' . $sortOrder);
                    Log::warning('Nenhuma avaliação encontrada para o local.', [
                        'place_id' => $placeId,
                        'sort_order' => $sortOrder,
                    ]);
                    return;
                }

                // Processa as avaliações
                $reviewsData = [];
                foreach ($data['result']['reviews'] as $review) {
                    $reviewsData[] = [
                        'google_review_id' => md5($review['author_name'] . $review['time']),
                        'author_name' => $review['author_name'],
                        'profile_photo' => $review['profile_photo_url'] ?? null, // Campo opcional
                        'rating' => $review['rating'],
                        'text' => $review['text'],
                        'time' => Carbon::createFromTimestamp($review['time']),
                    ];
                }

                // Insere ou atualiza as avaliações no banco de dados
                foreach ($reviewsData as $reviewData) {
                    Review::updateOrCreate(
                        ['google_review_id' => $reviewData['google_review_id']],
                        $reviewData
                    );
                }

                $this->info('Avaliações obtidas e armazenadas com sucesso para a ordem: ' . $sortOrder);

                // Verifica se há mais páginas de resultados
                $nextPageToken = $data['result']['next_page_token'] ?? null;
                $requestCount++;

                // Aguarda 2 segundos antes de fazer a próxima requisição (necessário para paginação)
                if ($nextPageToken) {
                    sleep(2);
                }

            } while ($nextPageToken && $requestCount < $maxRequests);

        } catch (\Exception $e) {
            $this->error('Ocorreu um erro: ' . $e->getMessage());
            Log::error('Erro ao buscar avaliações do Google.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
