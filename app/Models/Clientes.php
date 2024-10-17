<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enderecos;

class Clientes extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'cpf_cnpj', 'telefone', 'email', 'endereco_id', 'tipo_cliente'];


    public function endereco()
    {
        return $this->belongsTo(Enderecos::class);
    }

}
