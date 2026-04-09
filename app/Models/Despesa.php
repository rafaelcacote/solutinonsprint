<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Despesa extends Model
{
    public const TIPO_FIXA = 'fixa';

    public const TIPO_VARIAVEL = 'variavel';

    public const STATUS_PENDENTE = 'pendente';

    public const STATUS_PAGO = 'pago';

    public const STATUS_VENCIDO = 'vencido';

    /** @var array<string, string> */
    public const TIPO_LABELS = [
        self::TIPO_FIXA => 'Fixa',
        self::TIPO_VARIAVEL => 'Variável',
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDENTE => 'Pendente',
        self::STATUS_PAGO => 'Pago',
        self::STATUS_VENCIDO => 'Vencido',
    ];

    protected $table = 'despesas';

    protected $fillable = [
        'categoria_despesa_id',
        'fornecedor_id',
        'descricao',
        'tipo',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'status',
        'forma_pagamento_id',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_vencimento' => 'date',
            'data_pagamento' => 'date',
        ];
    }

    public function categoriaDespesa(): BelongsTo
    {
        return $this->belongsTo(CategoriaDespesa::class, 'categoria_despesa_id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function formaPagamento(): BelongsTo
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }

    public function movimentacoesCaixa(): HasMany
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'despesa_id');
    }

    public static function tipoBadgeColor(string $tipo): string
    {
        return match ($tipo) {
            self::TIPO_FIXA => 'primary',
            self::TIPO_VARIAVEL => 'info',
            default => 'light',
        };
    }

    public static function statusBadgeColor(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDENTE => 'warning',
            self::STATUS_PAGO => 'success',
            self::STATUS_VENCIDO => 'error',
            default => 'light',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TIPO_LABELS[$this->tipo] ?? $this->tipo;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
