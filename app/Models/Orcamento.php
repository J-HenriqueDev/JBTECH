<?php

namespace App\Models;
use App\Models\Clientes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'data', 'validade', 'observacoes', 'valor_total', 'valor_servico'];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
    }

    public function setValorServicoAttribute($value)
    {
        $this->attributes['valor_servico'] = str_replace(',', '.', str_replace('.', '', $value));
    }


    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'orcamento_produto')->withPivot(['quantidade', 'valor_unitario']);
    }
}
