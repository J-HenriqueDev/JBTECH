<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoCodigo extends Model
{
    use HasFactory;

    protected $table = 'produto_codigos';

    protected $fillable = [
        'produto_id',
        'codigo',
        'descricao',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
