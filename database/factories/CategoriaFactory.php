<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->word, // Gera uma palavra aleatória para o nome da categoria
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
