<?php

namespace Database\Seeders;

use App\Models\Clientes;
use Illuminate\Database\Seeder;

class ClientesTableSeeder extends Seeder
{
    public function run()
    {
        // Cria 10 clientes fictícios
        Clientes::factory()->count(10)->create();
    }
}
