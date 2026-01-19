<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use Carbon\Carbon;

class ReviewsTableSeeder extends Seeder
{
    public function run()
    {
        // Limpar avaliações antigas para evitar duplicidade ou dados inconsistentes
        Review::truncate();

        $reviews = [
            [
                'author_name' => 'Lucas Soares',
                'profile_photo' => 'assets/img/avatars/1.png',
                'rating' => 5,
                'text' => 'Atendimento de excelência e serviço prestado é impecável! Recomendo!',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Anderson Silva',
                'profile_photo' => 'assets/img/avatars/2.png',
                'rating' => 5,
                'text' => 'Muito bom ,parabéns pelo atendimento e pelo serviço prestado',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Danielle Costa',
                'profile_photo' => 'assets/img/avatars/3.png',
                'rating' => 5,
                'text' => 'Minha experiência foi ótima!! Super atencioso no atendimento, rápido e caso eu precise novamente irei solicitar seus serviços. 😉',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Juciana',
                'profile_photo' => 'assets/img/avatars/4.png',
                'rating' => 5,
                'text' => 'Atendimento excelente, profissional altamente competente. Serviços de primeira qualidade, além de preço justo e eficiência no serviço prestado. Com certeza, o melhor da região.',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Schellon',
                'profile_photo' => 'assets/img/avatars/5.png',
                'rating' => 5,
                'text' => 'Excelente! Atendimento incrível e diferenciado, juntamente com um comprometimento extraordinário com os clientes e consumidores, nada a reclamar, só elogios.',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Godri Sevach',
                'profile_photo' => 'assets/img/avatars/6.png',
                'rating' => 5,
                'text' => 'Excelente profissional. Recomendo seus serviços. Competente e atencioso, um pós atendimento excelente também.',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'fernando silvestre',
                'profile_photo' => 'assets/img/avatars/7.png',
                'rating' => 5,
                'text' => 'Excelente profissional, tem um atendimento muito bom, preço justo e conhecimento do que está fazendo.',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Jucilene Lustosa',
                'profile_photo' => 'assets/img/avatars/8.png',
                'rating' => 5,
                'text' => 'Ótimo atendimento! Rapidez e eficiência no trabalho!',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Juciana e Fernando',
                'profile_photo' => 'assets/img/avatars/9.png',
                'rating' => 5,
                'text' => 'Serviço muito bom! Profissional muito correto e competente. Ótimo preço!!',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Rosely Rosely',
                'profile_photo' => 'assets/img/avatars/10.png',
                'rating' => 5,
                'text' => 'Muito obrigada, resolveu meu problema de forma rápida e por ótimo preço.',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'regiane lustosa',
                'profile_photo' => 'assets/img/avatars/11.png',
                'rating' => 5,
                'text' => 'Excelente trabalho!!! Competência e agilidade!!! Parabéns!!!',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Alessandro Cesar',
                'profile_photo' => 'assets/img/avatars/12.png',
                'rating' => 5,
                'text' => 'Fazem um ótimo serviço, recomendo',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'marciano pereira lustrosa lustosa',
                'profile_photo' => 'assets/img/avatars/13.png',
                'rating' => 5,
                'text' => 'Excelente trabalho, atualização de sistema e instalação de impressoras👏',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Nicolas Souza',
                'profile_photo' => 'assets/img/avatars/14.png',
                'rating' => 5,
                'text' => 'Ótimo atendimento.',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Bianca Paiva',
                'profile_photo' => 'assets/img/avatars/15.png',
                'rating' => 5,
                'text' => 'Trabalho com excelência!',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Garuda Gatack',
                'profile_photo' => 'assets/img/avatars/16.png',
                'rating' => 5,
                'text' => 'Muito top.',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'JACARÉ',
                'profile_photo' => 'assets/img/avatars/17.png',
                'rating' => 5,
                'text' => 'Execelente loja',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'luiz antonio viana dos santos VIANA',
                'profile_photo' => 'assets/img/avatars/18.png',
                'rating' => 5,
                'text' => 'Exemplo de profissional!',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Marlos Lustosa',
                'profile_photo' => 'assets/img/avatars/19.png',
                'rating' => 5,
                'text' => 'Excelente 🤝',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'lilderickindalaje',
                'profile_photo' => 'assets/img/avatars/20.png',
                'rating' => 5,
                'text' => 'brabo dmss',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Eliza Vitoria',
                'profile_photo' => 'assets/img/avatars/1.png',
                'rating' => 5,
                'text' => '',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => '???',
                'profile_photo' => 'assets/img/avatars/2.png',
                'rating' => 5,
                'text' => '',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'João Lustoza',
                'profile_photo' => 'assets/img/avatars/3.png',
                'rating' => 5,
                'text' => '',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Harllison Fonseca Ferraz',
                'profile_photo' => 'assets/img/avatars/4.png',
                'rating' => 5,
                'text' => '',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Luis Carlos',
                'profile_photo' => 'assets/img/avatars/5.png',
                'rating' => 5,
                'text' => '',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Debora Maia',
                'profile_photo' => 'assets/img/avatars/6.png',
                'rating' => 5,
                'text' => '',
                'time' => Carbon::now()->subYear()
            ],
            [
                'author_name' => 'Bria Paiva',
                'profile_photo' => 'assets/img/avatars/7.png',
                'rating' => 5,
                'text' => '',
                'time' => Carbon::now()->subYear()
            ],
        ];

        foreach ($reviews as $index => $review) {
            Review::create([
                'google_review_id' => (string) ($index + 1),
                'author_name' => $review['author_name'],
                'profile_photo' => $review['profile_photo'],
                'rating' => $review['rating'],
                'text' => $review['text'],
                'time' => $review['time'],
            ]);
        }
    }
}
