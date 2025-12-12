<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sangria extends Model
{
    use HasFactory;

    protected $fillable = [
        'caixa_id',
        'user_id',
        'valor',
        'observacoes',
        'data_hora',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_hora' => 'datetime',
    ];

    public function caixa()
    {
        return $this->belongsTo(Caixa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


