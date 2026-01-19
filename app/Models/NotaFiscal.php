<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaFiscal extends Model
{
    use HasFactory;

    protected $table = 'notas_fiscais';

    protected $fillable = [
        'venda_id',
        'cliente_id',
        'numero_nfe',
        'chave_acesso',
        'serie',
        'natureza_operacao',
        'tipo_documento',
        'finalidade',
        'status',
        'xml',
        'xml_cancelamento',
        'protocolo',
        'motivo_rejeicao',
        'valor_total',
        'data_emissao',
        'data_saida',
        'data_vencimento',
        'observacoes',
        'dados_emitente',
        'dados_destinatario',
        'dados_pagamento',
        'produtos',
    ];

    protected $casts = [
        'dados_emitente' => 'array',
        'dados_destinatario' => 'array',
        'dados_pagamento' => 'array',
        'produtos' => 'array',
        'valor_total' => 'decimal:2',
        'data_emissao' => 'datetime',
        'data_saida' => 'datetime',
        'data_vencimento' => 'date',
    ];

    /**
     * Relacionamento com a venda
     */
    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    /**
     * Relacionamento com o cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
    }

    /**
     * Verifica se a NF-e está autorizada
     */
    public function isAutorizada()
    {
        return $this->status === 'autorizada';
    }

    /**
     * Verifica se a NF-e pode ser cancelada
     */
    public function podeCancelar()
    {
        return $this->status === 'autorizada';
    }
}
