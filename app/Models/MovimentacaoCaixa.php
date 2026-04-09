<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimentacaoCaixa extends Model
{
    public const TIPO_ENTRADA = 'entrada';

    public const TIPO_SAIDA = 'saida';

    public const ORIGEM_VENDA = 'venda';

    public const ORIGEM_DESPESA = 'despesa';

    public const ORIGEM_AJUSTE = 'ajuste';

    public const ORIGEM_RETIRADA = 'retirada';

    public const ORIGEM_APORTE = 'aporte';

    /** @var array<string, string> */
    public const TIPO_LABELS = [
        self::TIPO_ENTRADA => 'Entrada',
        self::TIPO_SAIDA => 'Saída',
    ];

    /** @var array<string, string> */
    public const ORIGEM_LABELS = [
        self::ORIGEM_VENDA => 'Venda',
        self::ORIGEM_DESPESA => 'Despesa',
        self::ORIGEM_AJUSTE => 'Ajuste',
        self::ORIGEM_RETIRADA => 'Retirada',
        self::ORIGEM_APORTE => 'Aporte',
    ];

    protected $table = 'movimentacoes_caixa';

    protected $fillable = [
        'tipo',
        'origem',
        'descricao',
        'valor',
        'forma_pagamento_id',
        'venda_id',
        'despesa_id',
        'data_movimentacao',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_movimentacao' => 'datetime',
        ];
    }

    public function formaPagamento(): BelongsTo
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }

    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }

    public function despesa(): BelongsTo
    {
        return $this->belongsTo(Despesa::class, 'despesa_id');
    }

    public static function tipoBadgeColor(string $tipo): string
    {
        return match ($tipo) {
            self::TIPO_ENTRADA => 'success',
            self::TIPO_SAIDA => 'error',
            default => 'light',
        };
    }

    public static function origemBadgeColor(string $origem): string
    {
        return match ($origem) {
            self::ORIGEM_VENDA => 'primary',
            self::ORIGEM_DESPESA => 'warning',
            self::ORIGEM_AJUSTE => 'info',
            self::ORIGEM_RETIRADA => 'error',
            self::ORIGEM_APORTE => 'success',
            default => 'light',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TIPO_LABELS[$this->tipo] ?? $this->tipo;
    }

    public function getOrigemLabelAttribute(): string
    {
        return self::ORIGEM_LABELS[$this->origem] ?? $this->origem;
    }
}
