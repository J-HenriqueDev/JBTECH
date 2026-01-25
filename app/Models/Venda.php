<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $fillable = [
      'cliente_id',
      'user_id',
      'caixa_id',
      'data_venda',
      'observacoes',
      'valor_total',
      'forma_pagamento',
      'valor_recebido',
      'troco',
      'numero_cupom',
      'sincronizado',
      'data_sincronizacao',
      'status',
      'bloqueado',
  ];

    protected $casts = [
        'data_venda' => 'date',
        'valor_total' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'troco' => 'decimal:2',
        'sincronizado' => 'boolean',
        'data_sincronizacao' => 'datetime',
        'bloqueado' => 'boolean',
    ];

    // Relacionamento com o cliente
    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    // Relacionamento com o usuário que fez a venda
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relacionamento com os produtos (tabela pivô)
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'produto_venda')
                    ->withPivot('quantidade', 'valor_unitario', 'valor_total')
                    ->withTimestamps();
    }

    // Relacionamento com notas fiscais
    public function notasFiscais()
    {
        return $this->hasMany(NotaFiscal::class);
    }

    // Relacionamento com notas fiscais de serviço
    public function notasFiscaisServico()
    {
        return $this->hasMany(NotaFiscalServico::class);
    }

    // Relacionamento com cobranças
    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class);
    }

    // Relacionamento com caixa
    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }
}
