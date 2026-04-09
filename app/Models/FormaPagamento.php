<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaPagamento extends Model
{
    public const TIPOS_LABELS = [
        'dinheiro' => 'Dinheiro',
        'pix' => 'Pix',
        'cartao' => 'Cartão',
        'transferencia' => 'Transferência',
        'outro' => 'Outro',
    ];

    protected $table = 'formas_pagamento';

    protected $fillable = [
        'nome',
        'tipo',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function recebimentosVenda(): HasMany
    {
        return $this->hasMany(RecebimentoVenda::class, 'forma_pagamento_id');
    }

    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class, 'forma_pagamento_id');
    }

    public function movimentacoesCaixa(): HasMany
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'forma_pagamento_id');
    }
}
