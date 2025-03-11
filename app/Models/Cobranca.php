<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobranca extends Model
{
    use HasFactory;

    protected $fillable = [
        'venda_id',
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

    // Relacionamento com a venda
    public function venda()
    {
        return $this->belongsTo(Venda::class);
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
