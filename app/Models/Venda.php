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
      'data_venda',
      'observacoes',
      'valor_total',
      // 'reference', // Referência da venda no PagSeguro
      // 'status' // Status do pagamento
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
}
