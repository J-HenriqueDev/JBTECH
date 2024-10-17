<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enderecos extends Model
{
    use HasFactory;
    protected $fillable = ['cep', 'endereco', 'bairro', 'cidade', 'estado'];

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}
