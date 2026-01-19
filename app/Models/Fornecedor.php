<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';

    protected $fillable = [
        'nome',
        'cnpj',
        'telefone',
        'email',
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'uf',
    ];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'produto_fornecedor')
                    ->withPivot('codigo_produto_fornecedor', 'preco_custo')
                    ->withTimestamps();
    }
}
