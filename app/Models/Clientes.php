<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;
    protected $fillable = ['nome', 'cpf', 'telefone', 'email', 'tipo_cliente'];

    public function endereco()
    {
        return $this->belongsTo(Endereco::class);
    }

    public function tipoCliente()
    {
        return $this->belongsTo(TipoCliente::class);
    }
}
