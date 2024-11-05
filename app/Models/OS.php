<?php

// app/Models/OS.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OS extends Model
{
    use HasFactory;

    protected $table = 'os';

    protected $fillable = [
        'cliente_id',
        'tipo_id',
        'data_de_entrada',
        'prazo_entrega',
        'problema_item',
        'acessorios',
        'senha_do_dispositivo',
        'modelo_do_dispositivo', // Novo campo
        'sn',                    // Novo campo
        'avarias',
        'fotos',
        'usuario_id'
    ];

    protected $casts = [
        'fotos' => 'array', // Para armazenar múltiplas fotos como um array
    ];

    // Definindo os tipos de equipamento disponíveis
    public const TIPOS_DE_EQUIPAMENTO = [
        'COMPUTADOR' => 'Computador',
        'NOTEBOOK' => 'Notebook',
        'IMPRESSORA' => 'Impressora',
        'DVR' => 'DVR',
        'CAMERA' => 'Câmera',
        'IMPRESSORA_TERMICA' => 'Impressora Térmica',
        'MACBOOK' => 'MacBook',
        'OUTROS' => 'Outros',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id'); // Altere 'cliente_id' para o nome real da sua chave estrangeira
    }
}
