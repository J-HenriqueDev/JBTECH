<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
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

            do {
                $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&key={$apiKey}&fields=reviews&reviews_sort={$sortOrder}" . ($nextPageToken ? "&pagetoken={$nextPageToken}" : '');
                $response = Http::get($url);
                $data = $response->json();

                if (isset($data['result']['reviews']) && count($data['result']['reviews']) > 0) {
                    $reviewsData = [];
                    foreach ($data['result']['reviews'] as $review) {
                        $reviewsData[] = [
                            'google_review_id' => md5($review['author_name'] . $review['time']),
                            'author_name' => $review['author_name'],
                            'profile_photo' => $review['profile_photo_url'],
                            'rating' => $review['rating'],
                            'text' => $review['text'],
                            'time' => Carbon::createFromTimestamp($review['time']),
                        ];
                    }

                    // Inserir em massa (mais eficiente para grandes volumes)
                    Review::insertOrIgnore($reviewsData);

                    $this->info('Avaliações obtidas e armazenadas com sucesso para a ordem: ' . $sortOrder);
                } else {
                    $this->error('Nenhuma avaliação encontrada ou erro na API.');
                }

                $nextPageToken = $data['result']['next_page_token'] ?? null;
                sleep(2); // Ajuste o tempo de espera conforme necessário

            } while ($nextPageToken);
        } catch (\Exception $e) {
            $this->error('Ocorreu um erro: ' . $e->getMessage());
        }
    }
}
