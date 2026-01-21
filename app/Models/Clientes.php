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
        'telefone_secundario',
        'email',
        'email_secundario',
        'endereco_id',
        'inscricao_estadual',
        'inscricao_municipal',
        'indicador_ie',
        'data_nascimento',
        'tipo_cliente',
        'suframa'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
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
