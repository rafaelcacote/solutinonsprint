<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaDespesa extends Model
{
    protected $table = 'categorias_despesa';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function despesas(): HasMany
    {
        return $this->hasMany(Despesa::class, 'categoria_despesa_id');
    }
}
