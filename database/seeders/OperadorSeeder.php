<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operador;

class OperadorSeeder extends Seeder
{
    public function run(): void
    {
        Operador::create([
            'codigo' => '001',
            'nome' => 'Operador Administrador',
            'senha' => '123456', // Será hashado automaticamente
            'ativo' => true,
        ]);

        Operador::create([
            'codigo' => '002',
            'nome' => 'Operador Caixa',
            'senha' => '123456',
            'ativo' => true,
        ]);
    }
}


