<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'fornecedor_id',
        'cliente_id',
        'local_compra',
        'data_compra',
        'data_prevista_entrega',
        'valor_total',
        'status',
        'tipo',
        'prioridade',
        'observacoes',
        'motivo_recusa',
        'user_id',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    protected $casts = [
        'data_compra' => 'date',
        'data_prevista_entrega' => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CompraItem::class);
    }
}
