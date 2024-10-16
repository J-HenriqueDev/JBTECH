<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReviewController extends Controller
{
    public function getReviews()
    {
        $apiKey = env('AIzaSyCj4dadtkl0gnXiaLBp-JZINLbgvA6Wn-Y'); // Certifique-se de adicionar sua chave da API no .env
        $placeId = '1811744932557779224'; // Substitua pelo ID do seu local
        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=reviews&key={$apiKey}";

        $response = Http::get($url);

        if ($response->successful()) {
            $reviews = $response->json()['result']['reviews'];

            // Aqui você pode salvar as avaliações no banco de dados ou processá-las conforme necessário
            // Exemplo:
            foreach ($reviews as $review) {
                // Salve cada avaliação no seu banco de dados
            }

            return response()->json($reviews);
        }

        return response()->json(['error' => 'Unable to fetch reviews'], 500);
    }
}
