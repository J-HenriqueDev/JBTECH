<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'servico_id',
        'descricao',
        'valor',
        'dia_vencimento',
        'data_inicio',
        'data_fim',
        'ativo',
        'frequencia',
        'forma_pagamento',
        'ultimo_faturamento',
        'proximo_faturamento',
        'codigo_servico',
        'codigo_nbs',
        'aliquota_iss',
        'iss_retido',
        'discriminacao_servico',
        'observacoes',
        'tipo',
        'qtd_parcelas',
        'parcela_atual',
        'dias_personalizados',
        'valor_total'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ultimo_faturamento' => 'date',
        'proximo_faturamento' => 'date',
        'ativo' => 'boolean',
        'iss_retido' => 'boolean',
        'valor' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'aliquota_iss' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    /**
     * Calcula a próxima data de faturamento baseada na frequência
     */
    public function calcularProximoFaturamento($dataBase = null)
    {
        $data = $dataBase ? Carbon::parse($dataBase) : Carbon::now();

        // Lógica para Parcelamento Personalizado (ex: dias 5 e 20)
        if (!empty($this->dias_personalizados)) {
            $dias = explode(',', $this->dias_personalizados);
            $dias = array_map('trim', $dias); // Remove espaços
            $dias = array_map('intval', $dias); // Converte para int
            sort($dias); // Garante ordem crescente (ex: 5, 20)

            $currentDay = $data->day;
            $nextDay = null;
            $nextMonth = false;

            // Procura o próximo dia no mesmo mês
            foreach ($dias as $d) {
                if ($d > $currentDay) {
                    $nextDay = $d;
                    break;
                }
            }

            // Se não achou no mesmo mês, pega o primeiro dia da lista no próximo mês
            if (!$nextDay) {
                $nextDay = $dias[0];
                $nextMonth = true;
            }

            $proximo = $data->copy();
            if ($nextMonth) {
                $proximo->addMonthNoOverflow(); // Avança mês mantendo dia se possível
            }

            // Define o dia com segurança (evita 30 de Fev)
            $daysInMonth = $proximo->daysInMonth;
            $proximo->day(min($nextDay, $daysInMonth));

            return $proximo;
        }

        $dia = (int) $this->dia_vencimento;

        // Se a data base for hoje ou passado, avança para o próximo ciclo
        // Se for null (primeiro faturamento), define baseado no dia de vencimento

        // Lógica simplificada: Avança 1 mês/período a partir da data base
        switch ($this->frequencia) {
            case 'mensal':
                $data->addMonth();
                break;
            case 'trimestral':
                $data->addMonths(3);
                break;
            case 'semestral':
                $data->addMonths(6);
                break;
            case 'anual':
                $data->addYear();
                break;
        }

        // Ajusta o dia
        // Se o mês não tem o dia (ex: 31 em Fev), o Carbon ajusta automaticamente para o último dia do mês?
        // ->addMonth() sem overflow: $data->addMonthNoOverflow();
        // Mas queremos forçar o dia de vencimento se possível.

        // Estratégia: Definir o dia e ver se muda o mês. Se mudar, volta para o último dia do mês anterior.
        $proximo = $data->copy()->day($dia);

        if ($proximo->month != $data->month) {
             $proximo = $data->copy()->lastOfMonth();
        }

        return $proximo;
    }
}
