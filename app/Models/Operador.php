<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Operador extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'codigo',
        'nome',
        'senha',
        'ativo',
        'user_id',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verificarSenha($senha)
    {
        return Hash::check($senha, $this->senha);
    }

    public function setSenhaAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['senha'] = Hash::make($value);
        }
    }
}
