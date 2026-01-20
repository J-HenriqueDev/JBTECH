<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_id',
        'produto_id',
        'descricao_livre',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'status',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
