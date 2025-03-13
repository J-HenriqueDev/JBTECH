<?php

namespace Database\Factories;

use App\Models\Clientes;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientesFactory extends Factory
{
    protected $model = Clientes::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->name,
            'cpf_cnpj' => $this->faker->unique()->numerify('###########'),
            'telefone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'endereco_id' => \App\Models\Enderecos::factory(), // Cria um endereço automaticamente
            'tipo_cliente' => $this->faker->randomElement(['fisica', 'juridica']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
