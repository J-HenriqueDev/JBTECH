<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaEntrada extends Model
{
    use HasFactory;

    protected $table = 'notas_entradas';

    protected $fillable = [
        'chave_acesso',
        'numero_nfe',
        'serie',
        'emitente_cnpj',
        'emitente_nome',
        'valor_total',
        'data_emissao',
        'status',
        'xml_content',
        'manifestacao',
        'user_id'
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'valor_total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
