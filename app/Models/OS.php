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
        'modelo_do_dispositivo',
        'sn',
        'avarias',
        'fotos',
        'usuario_id',
        'status',
        'observacoes',
        'valor_servico',
        'data_conclusao'
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
    
    // Definindo os status disponíveis
    public const STATUS = [
        'pendente' => 'Pendente',
        'em_andamento' => 'Em Andamento',
        'aguardando_peca' => 'Aguardando Peça',
        'concluida' => 'Concluída',
        'entregue' => 'Entregue',
        'cancelada' => 'Cancelada',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id'); // Altere 'cliente_id' para o nome real da sua chave estrangeira
    }
}
