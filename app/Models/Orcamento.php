<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Orcamento extends Model
{
    public const STATUS_ABERTO = 'aberto';

    public const STATUS_APROVADO = 'aprovado';

    public const STATUS_RECUSADO = 'recusado';

    public const STATUS_VENCIDO = 'vencido';

    public const STATUS_CONVERTIDO = 'convertido';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_ABERTO => 'Aberto',
        self::STATUS_APROVADO => 'Aprovado',
        self::STATUS_RECUSADO => 'Recusado',
        self::STATUS_VENCIDO => 'Vencido',
        self::STATUS_CONVERTIDO => 'Convertido',
    ];

    protected $table = 'orcamentos';

    protected $fillable = [
        'cliente_id',
        'numero',
        'data_orcamento',
        'status',
        'subtotal',
        'desconto',
        'total',
        'validade_dias',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_orcamento' => 'datetime',
            'subtotal' => 'decimal:2',
            'desconto' => 'decimal:2',
            'total' => 'decimal:2',
            'validade_dias' => 'integer',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(OrcamentoItem::class, 'orcamento_id');
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class, 'orcamento_id');
    }

    public static function statusBadgeColor(string $status): string
    {
        return match ($status) {
            self::STATUS_ABERTO => 'info',
            self::STATUS_APROVADO => 'success',
            self::STATUS_RECUSADO => 'error',
            self::STATUS_VENCIDO => 'warning',
            self::STATUS_CONVERTIDO => 'primary',
            default => 'light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
