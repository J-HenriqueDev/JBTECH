<?php

namespace Database\Factories;

use App\Models\Enderecos;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnderecosFactory extends Factory
{
    protected $model = Enderecos::class;

    public function definition()
    {
        return [
            'cep' => $this->faker->postcode,
            'endereco' => $this->faker->streetAddress,
            'numero' => $this->faker->buildingNumber,
            'bairro' => $this->faker->citySuffix,
            'cidade' => $this->faker->city,
            'estado' => $this->faker->stateAbbr,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
