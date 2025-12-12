<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enderecos;

class Clientes extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 
        'cpf_cnpj', 
        'telefone', 
        'email', 
        'endereco_id', 
        'tipo_cliente',
        'inscricao_estadual',
        'data_nascimento'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'tipo_cliente' => 'integer',
    ];

    public function endereco()
    {
        return $this->belongsTo(Enderecos::class);
    }
    
    public function vendas()
    {
        return $this->hasMany(Venda::class, 'cliente_id');
    }
    
    public function orcamentos()
    {
        return $this->hasMany(Orcamento::class, 'cliente_id');
    }
    
    public function ordensServico()
    {
        return $this->hasMany(OS::class, 'cliente_id');
    }
    
    public function cobrancas()
    {
        return $this->hasManyThrough(Cobranca::class, Venda::class, 'cliente_id', 'venda_id');
    }

}
