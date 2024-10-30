<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nome'];

    // Definindo o relacionamento com os produtos
    public function produtos()
    {
        return $this->hasMany(Produto::class); // Altere 'Produto' se seu modelo tiver um nome diferente
    }
}
