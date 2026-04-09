<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendaItem extends Model
{
    protected $table = 'venda_itens';

    protected $fillable = [
        'venda_id',
        'item_id',
        'categoria_id',
        'descricao_item',
        'quantidade',
        'unidade_medida',
        'valor_unitario',
        'subtotal',
        'custo_unitario',
        'custo_total',
        'largura',
        'altura',
        'metragem',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'decimal:2',
            'valor_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'custo_unitario' => 'decimal:2',
            'custo_total' => 'decimal:2',
            'largura' => 'decimal:2',
            'altura' => 'decimal:2',
            'metragem' => 'decimal:2',
        ];
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
