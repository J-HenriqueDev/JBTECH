<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaturezaOperacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'tipo',
        'cfop_estadual',
        'cfop_interestadual',
        'cfop_exterior',
        'padrao',
        'calcula_custo',
        'movimenta_estoque',
        'gera_financeiro',
        'finNFe',
        'indPres',
        'consumidor_final'
    ];
}
