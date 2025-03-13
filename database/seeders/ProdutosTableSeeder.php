<?php

namespace Database\Seeders;

use App\Models\Produto;
use Illuminate\Database\Seeder;

class ProdutosTableSeeder extends Seeder
{
    public function run()
    {
        // Cria 20 produtos fictícios
        Produto::factory()->count(20)->create();
    }
}
