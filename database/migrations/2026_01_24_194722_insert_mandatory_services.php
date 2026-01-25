<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        $servicos = [
            [
                'nome' => 'Prestação de serviço avulsa',
                'codigo_servico' => '010701',
                'codigo_nbs' => '115013000',
                'aliquota_iss' => 0.00,
                'iss_retido' => false,
                'discriminacao_padrao' => "SERVIÇO PRESTADO DE FORMA AVULSA AO CLIENTE\r\n\r\n\r\nChave Pix 54819910000120",
                'observacoes' => null,
                'ativo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nome' => 'Contrato Mensal',
                'codigo_servico' => '010701',
                'codigo_nbs' => '115013000',
                'aliquota_iss' => 0.00,
                'iss_retido' => false,
                'discriminacao_padrao' => "Contrato mensal de prestação de serviços de TI\r\n\r\nChave Pix 54819910000120",
                'observacoes' => null,
                'ativo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        foreach ($servicos as $servico) {
            DB::table('servicos')->updateOrInsert(
                ['nome' => $servico['nome']], // Verifica pelo nome para evitar duplicidade
                $servico
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('servicos')->whereIn('nome', [
            'Prestação de serviço avulsa',
            'Contrato Mensal'
        ])->delete();
    }
};
