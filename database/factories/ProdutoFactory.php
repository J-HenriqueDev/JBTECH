<?php

namespace Database\Factories;

use App\Models\Produto;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProdutoFactory extends Factory
{
    protected $model = Produto::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->word,
            'preco_custo' => $this->faker->randomFloat(2, 10, 100),
            'preco_venda' => $this->faker->randomFloat(2, 20, 200),
            'codigo_barras' => $this->faker->unique()->ean13,
            'ncm' => $this->faker->numerify('########'),
            'estoque' => $this->faker->numberBetween(0, 100),
            'categoria_id' => Categoria::factory(), // Cria uma categoria automaticamente
            'usuario_id' => User::factory(), // Cria um usuário automaticamente
            'fornecedor_cnpj' => $this->faker->numerify('##############'),
            'fornecedor_nome' => $this->faker->company,
            'fornecedor_telefone' => $this->faker->phoneNumber,
            'fornecedor_email' => $this->faker->companyEmail,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
