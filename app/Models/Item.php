<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $table = 'itens';

    protected $fillable = [
        'categoria_id',
        'tipo',
        'nome',
        'descricao',
        'unidade_medida',
        'modo_preco',
        'preco_venda',
        'custo_estimado',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco_venda' => 'decimal:2',
            'custo_estimado' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function regrasPreco(): HasMany
    {
        return $this->hasMany(RegraPreco::class, 'item_id');
    }

    public function orcamentoItens(): HasMany
    {
        return $this->hasMany(OrcamentoItem::class, 'item_id');
    }

    public function vendaItens(): HasMany
    {
        return $this->hasMany(VendaItem::class, 'item_id');
    }
}
