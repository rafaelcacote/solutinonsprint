<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegraPreco extends Model
{
    protected $table = 'regras_preco';

    protected $fillable = [
        'item_id',
        'quantidade_minima',
        'quantidade_maxima',
        'valor_unitario',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_minima' => 'integer',
            'quantidade_maxima' => 'integer',
            'valor_unitario' => 'decimal:2',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
