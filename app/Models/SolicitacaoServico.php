<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoServico extends Model
{
    use HasFactory;

    protected $table = 'solicitacoes_servicos';

    protected $fillable = [
        'cliente_id',
        'canal_atendimento',
        'data_solicitacao',
        'tipo_atendimento',
        'descricao',
        'pendencias',
        'status',
    ];

    protected $casts = [
        'data_solicitacao' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }
}
