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
        // Consultando as avaliações
        $reviews = Review::all();

        // Registrando no log quando as avaliações são consultadas
        Log::info('Avaliações consultadas na landing page.', [
            'count' => $reviews->count(),
            'timestamp' => now(),
        ]);

        $pageConfigs = ['myLayout' => 'front'];
        return view('content.pages.landing-page', [
            'pageConfigs' => $pageConfigs,
            'reviews' => $reviews, // Passando as avaliações para a view
        ]);
    }
}
