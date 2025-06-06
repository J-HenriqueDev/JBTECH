<?php

namespace App\Models;
use App\Models\Categoria;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
      'nome',
      'preco_custo',
      'preco_venda',
      'codigo_barras',
      'ncm',
      'estoque',
      'categoria_id',
      'usuario_id',
      'fornecedor_cnpj',
      'fornecedor_nome',
      'fornecedor_telefone',
      'fornecedor_email',
  ];


    // Definindo o relacionamento com a categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class); // Altere 'Categoria' se seu modelo tiver um nome diferente
    }

    public function usuario()
    {
        return $this->belongsTo(User::class); // Relacionamento com usuário
    }

    public function vendas()
    {
        return $this->belongsToMany(Venda::class, 'produto_venda')
            ->withPivot('quantidade', 'valor_unitario', 'valor_total')
            ->withTimestamps();
    }
}
