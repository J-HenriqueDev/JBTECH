<?php

namespace App\Models;
use App\Http\Controllers\ClientesController;
use App\Models\Clientes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enderecos extends Model
{
    use HasFactory;
    protected $fillable = ['cep', 'endereco','numero', 'bairro', 'cidade', 'estado'];

    public function clientes()
    {
        return $this->hasMany(Clientes::class);
    }
}
