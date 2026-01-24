<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;

    // Status possíveis
    const STATUS_PENDENTE = 'pendente';
    const STATUS_AUTORIZADO = 'autorizado';
    const STATUS_RECUSADO = 'recusado';

    protected $fillable = [
        'cliente_id',
        'data',
        'validade',
        'observacoes',
        'valor_total',
        'status',
        'user_id',
        'formas_pagamento',
        'parcelas_boleto',
        'periodicidade_boleto',
    ];

    protected $casts = [
        'data' => 'date',
        'validade' => 'date',
        'valor_total' => 'decimal:2',
        'formas_pagamento' => 'array',
    ];

    /**
     * Relacionamento com o modelo Clientes.
     */
    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
    }

    /**
     * Relacionamento com o usuário que criou o orçamento.
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Relacionamento muitos-para-muitos com o modelo Produto.
     * Inclui as colunas da tabela pivô (quantidade, valor_unitario, valor_total).
     */
    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'orcamento_produto')
            ->withPivot(['quantidade', 'valor_unitario', 'valor_total']);
    }

    /**
     * Scope para orçamentos pendentes
     */
    public function scopePendentes($query)
    {
        return $query->where('status', self::STATUS_PENDENTE);
    }

    /**
     * Scope para orçamentos autorizados
     */
    public function scopeAutorizados($query)
    {
        return $query->where('status', self::STATUS_AUTORIZADO);
    }

    /**
     * Scope para orçamentos recusados
     */
    public function scopeRecusados($query)
    {
        return $query->where('status', self::STATUS_RECUSADO);
    }

    /**
     * Verifica se o orçamento está vencido
     */
    public function isVencido()
    {
        return $this->validade < now();
    }

    /**
     * Verifica se pode ser autorizado (tem estoque suficiente)
     */
    public function podeSerAutorizado()
    {
        foreach ($this->produtos as $produto) {
            // Ignorar serviços (tipo_item == '09')
            if ($produto->tipo_item === '09') {
                continue;
            }

            if ($produto->estoque < $produto->pivot->quantidade) {
                return false;
            }
        }
        return true;
    }
}
