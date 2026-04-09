<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

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

    public function itens(): HasMany
    {
        return $this->hasMany(Item::class, 'categoria_id');
    }

    public function vendaItens(): HasMany
    {
        return $this->hasMany(VendaItem::class, 'categoria_id');
    }
}
