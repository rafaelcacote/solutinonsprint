<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'cpf_cnpj',
        'observacoes',
    ];

    public function orcamentos(): HasMany
    {
        return $this->hasMany(Orcamento::class, 'cliente_id');
    }

    public function vendas(): HasMany
    {
        return $this->hasMany(Venda::class, 'cliente_id');
    }
}
