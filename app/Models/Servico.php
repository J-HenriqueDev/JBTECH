<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'codigo_servico',
        'codigo_nbs',
        'aliquota_iss',
        'iss_retido',
        'discriminacao_padrao',
        'observacoes',
        'ativo'
    ];

    protected $casts = [
        'iss_retido' => 'boolean',
        'ativo' => 'boolean',
        'aliquota_iss' => 'decimal:2',
    ];

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }
}
