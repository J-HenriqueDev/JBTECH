<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id',
        'contrato_id',
        'metodo_pagamento',
        'status',
        'valor',
        'data_vencimento',
        'codigo_pix',
        'link_boleto',
        'link_pagamento',
        'recorrente',
        'frequencia_recorrencia',
        'proxima_cobranca',
        'enviar_email',
        'enviar_whatsapp',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'proxima_cobranca' => 'datetime',
        'valor' => 'decimal:2',
    ];

    // Relacionamento com a venda
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    // Relacionamento com o contrato
    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    public function getClienteAttribute()
    {
        if ($this->venda) {
            return $this->venda->cliente;
        }
        if ($this->contrato) {
            return $this->contrato->cliente;
        }
        return null;
    }

    // Verifica se a cobrança é recorrente
    public function isRecorrente()
    {
        return $this->recorrente;
    }

    // Agenda a próxima cobrança recorrente
    public function agendarProximaCobranca()
    {
        if ($this->recorrente && $this->frequencia_recorrencia) {
            $this->proxima_cobranca = now()->add($this->frequencia_recorrencia);
            $this->save();
        }
    }
}
