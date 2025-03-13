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
    ];

    /**
     * Relacionamento com o modelo Clientes.
     */
    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
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
}
