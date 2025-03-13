<?php

namespace Database\Factories;

use App\Models\Clientes;
use App\Models\Enderecos;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Clientes::class;

    public function definition()
    {
        return [
            'nome' => $this->faker->name,
            'cpf_cnpj' => $this->faker->unique()->numerify('###########'), // CPF ou CNPJ fictício
            'telefone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'endereco_id' => Enderecos::factory(), // Cria um endereço automaticamente
            'tipo_cliente' => $this->faker->randomElement(['fisica', 'juridica']), // Tipo de cliente
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
