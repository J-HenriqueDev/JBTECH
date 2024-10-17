<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
      'nome_produto',
      'codigo_barras',
      'preco_venda',
      'categoria_id', // Renomeado de subgrupo para categoria
      'local_impressao',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function adicionais()
    {
        return $this->belongsToMany(Adicional::class);
    }

}
