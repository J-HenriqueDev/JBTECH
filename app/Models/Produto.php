<?php

namespace App\Models;

use App\Models\Categoria;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Loggable;

class Produto extends Model
{
    use HasFactory, Loggable;

    protected $fillable = [
        'nome',
        'preco_custo',
        'preco_venda',
        'codigo_barras',
        'ncm',
        'estoque',
        'categoria_id',
        'usuario_id',
        'fornecedor_cnpj',
        'fornecedor_nome',
        'fornecedor_telefone',
        'fornecedor_email',
        'sincronizado',
        'ultima_sincronizacao',
        'ativo_pdv',
        'ativo',
        'tipo_item',
        'codigo_servico',
        'estoque_minimo',
        'estoque_maximo',
        'localizacao',
        'peso_liquido',
        'peso_bruto',
        'largura',
        'altura',
        'comprimento',
        'observacoes_internas',
        // Campos fiscais
        'cest',
        'cfop_interno',
        'cfop_externo',
        'unidade_comercial',
        'unidade_tributavel',
        'origem',
        'csosn_icms',
        'cst_icms',
        'cst_pis',
        'cst_cofins',
        'aliquota_icms',
        'aliquota_pis',
        'aliquota_cofins',
        'perc_icms_fcp',
        // Campos de Preços e Promoção
        'preco_atacado',
        'categorizado_por_ia',
        'qtd_min_atacado',
        'preco_promocional',
        'inicio_promocao',
        'fim_promocao',
        'categorizado_por_ia',
    ];


    // Definindo o relacionamento com a categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class); // Altere 'Categoria' se seu modelo tiver um nome diferente
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class); // Relacionamento com usuário
    }

    public function vendas()
    {
        return $this->belongsToMany(Venda::class, 'produto_venda')
            ->withPivot('quantidade', 'valor_unitario', 'valor_total')
            ->withTimestamps();
    }

    public function fornecedores()
    {
        return $this->belongsToMany(Fornecedor::class, 'produto_fornecedor')
            ->withPivot('codigo_produto_fornecedor', 'preco_custo')
            ->withTimestamps();
    }

    public function codigosAdicionais()
    {
        return $this->hasMany(ProdutoCodigo::class);
    }

    /**
     * Verifica se o produto é um serviço.
     *
     * @return bool
     */
    public function isService(): bool
    {
        return $this->id === 1 ||
               $this->tipo_item === '09' ||
               !is_null($this->servico_id);
    }

    protected $casts = [
        'sincronizado' => 'boolean',
        'ativo_pdv' => 'boolean',
        'ultima_sincronizacao' => 'datetime',
    ];
}
