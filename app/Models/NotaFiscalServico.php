<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaFiscalServico extends Model
{
    use HasFactory;

    protected $table = 'notas_fiscais_servico';

    protected $fillable = [
        'cliente_id',
        'numero_rps',
        'serie_rps',
        'numero_nfse',
        'chave_acesso',
        'data_emissao',
        'valor_servico',
        'valor_deducoes',
        'valor_iss',
        'aliquota_iss',
        'iss_retido',
        'valor_pis',
        'valor_cofins',
        'valor_inss',
        'valor_ir',
        'valor_csll',
        'valor_total',
        'discriminacao',
        'codigo_servico',
        'cnae',
        'municipio_prestacao',
        'status',
        'xml_envio',
        'xml_retorno',
        'link_nfse',
        'motivo_rejeicao',
        'user_id'
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'iss_retido' => 'boolean',
        'valor_servico' => 'decimal:2',
        'valor_deducoes' => 'decimal:2',
        'valor_iss' => 'decimal:2',
        'aliquota_iss' => 'decimal:2',
        'valor_pis' => 'decimal:2',
        'valor_cofins' => 'decimal:2',
        'valor_inss' => 'decimal:2',
        'valor_ir' => 'decimal:2',
        'valor_csll' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
