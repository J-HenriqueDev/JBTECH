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
        'data_vencimento',
        'data_pagamento',
        'status',
        'origem',
        'numero_documento',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
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
