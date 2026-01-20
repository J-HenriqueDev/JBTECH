<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagar extends Model
{
    use HasFactory;

    protected $table = 'contas_pagar';

    protected $fillable = [
        'fornecedor_id',
        'nota_entrada_id',
        'descricao',
        'valor',
        'valor_pago',
        'data_vencimento',
        'data_pagamento',
        'metodo_pagamento',
        'status',
        'origem',
        'numero_documento',
        'observacoes',
        'recorrente',
        'frequencia',
        'dia_vencimento',
        'proximo_vencimento',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'proximo_vencimento' => 'date',
        'recorrente' => 'boolean',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function notaEntrada()
    {
        return $this->belongsTo(NotaEntrada::class);
    }
}
