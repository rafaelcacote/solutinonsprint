<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    public const STATUS_ABERTA = 'aberta';

    public const STATUS_CONCLUIDA = 'concluida';

    public const STATUS_CANCELADA = 'cancelada';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_ABERTA => 'Aberta',
        self::STATUS_CONCLUIDA => 'Concluída',
        self::STATUS_CANCELADA => 'Cancelada',
    ];

    protected $table = 'vendas';

    protected $fillable = [
        'cliente_id',
        'orcamento_id',
        'numero',
        'data_venda',
        'status',
        'subtotal',
        'desconto',
        'total',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_venda' => 'datetime',
            'subtotal' => 'decimal:2',
            'desconto' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(VendaItem::class, 'venda_id');
    }

    public function recebimentos(): HasMany
    {
        return $this->hasMany(RecebimentoVenda::class, 'venda_id');
    }

    public function movimentacoesCaixa(): HasMany
    {
        return $this->hasMany(MovimentacaoCaixa::class, 'venda_id');
    }

    public static function statusBadgeColor(string $status): string
    {
        return match ($status) {
            self::STATUS_ABERTA => 'info',
            self::STATUS_CONCLUIDA => 'success',
            self::STATUS_CANCELADA => 'error',
            default => 'light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Soma dos recebimentos (relação carregada, query com withSum como `total_recebido_sum`, ou consulta direta).
     */
    public function totalRecebido(): float
    {
        if (array_key_exists('recebimentos_sum_valor', $this->attributes) && $this->attributes['recebimentos_sum_valor'] !== null) {
            return round((float) $this->attributes['recebimentos_sum_valor'], 2);
        }

        if ($this->relationLoaded('recebimentos')) {
            return round((float) $this->recebimentos->sum('valor'), 2);
        }

        return round((float) $this->recebimentos()->sum('valor'), 2);
    }

    public function saldoRestante(): float
    {
        $total = (float) $this->total;
        $recebido = $this->totalRecebido();

        return round(max(0, $total - $recebido), 2);
    }
}
