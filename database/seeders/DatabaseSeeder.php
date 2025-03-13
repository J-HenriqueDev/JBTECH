<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Verifica se o usuário já existe
        if (!User::where('email', 'test@example.com')->exists()) {
            // Cria o usuário de teste
            User::factory()->withPersonalTeam()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Executa os seeders na ordem correta
        $this->call([
            ClientesTableSeeder::class, // Cria clientes
            ProdutosTableSeeder::class, // Cria produtos
            OrcamentosTableSeeder::class, // Cria orçamentos
            VendaSeeder::class,
        ]);
    }
}
