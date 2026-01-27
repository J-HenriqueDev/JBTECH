<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoHistorico extends Model
{
    use HasFactory;

    protected $table = 'solicitacao_historicos';

    protected $fillable = [
        'solicitacao_id',
        'user_id',
        'acao',
        'status_anterior',
        'status_novo',
        'observacao',
    ];

    public function solicitacao()
    {
        return $this->belongsTo(SolicitacaoServico::class, 'solicitacao_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
