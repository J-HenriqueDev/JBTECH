<?php

namespace Database\Factories;

use App\Models\Orcamento;
use App\Models\Clientes;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrcamentoFactory extends Factory
{
    protected $model = Orcamento::class;

    public function definition()
    {
        return [
            'cliente_id' => Clientes::factory(), // Cria um cliente automaticamente
            'data' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'validade' => $this->faker->dateTimeBetween('now', '+30 days'),
            'observacoes' => $this->faker->sentence,
            'valor_total' => 0, // Será atualizado após a criação
            'status' => $this->faker->randomElement(['pendente', 'autorizado', 'recusado']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
