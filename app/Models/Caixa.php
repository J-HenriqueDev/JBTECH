<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data_abertura',
        'hora_abertura',
        'data_fechamento',
        'hora_fechamento',
        'valor_abertura',
        'valor_total_vendas',
        'valor_total_sangrias',
        'valor_total_suprimentos',
        'valor_esperado',
        'valor_fechamento',
        'diferenca',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'data_abertura' => 'date',
        'data_fechamento' => 'date',
        'hora_abertura' => 'datetime',
        'hora_fechamento' => 'datetime',
        'valor_abertura' => 'decimal:2',
        'valor_total_vendas' => 'decimal:2',
        'valor_total_sangrias' => 'decimal:2',
        'valor_total_suprimentos' => 'decimal:2',
        'valor_esperado' => 'decimal:2',
        'valor_fechamento' => 'decimal:2',
        'diferenca' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }

    public function sangrias()
    {
        return $this->hasMany(Sangria::class);
    }

    public function suprimentos()
    {
        return $this->hasMany(Suprimento::class);
    }

    public function calcularValorEsperado()
    {
        $this->valor_esperado = $this->valor_abertura 
            + $this->valor_total_vendas 
            + $this->valor_total_suprimentos 
            - $this->valor_total_sangrias;
        return $this->valor_esperado;
    }
}


