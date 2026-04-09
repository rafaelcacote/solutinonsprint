<?php

namespace Database\Seeders;

use App\Models\FormaPagamento;
use Illuminate\Database\Seeder;

class FormasPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formas = [
            ['nome' => 'Dinheiro', 'tipo' => 'dinheiro', 'ativo' => true],
            ['nome' => 'Pix', 'tipo' => 'pix', 'ativo' => true],
            ['nome' => 'Cartão Débito', 'tipo' => 'cartao', 'ativo' => true],
            ['nome' => 'Cartão Crédito', 'tipo' => 'cartao', 'ativo' => true],
            ['nome' => 'Transferência', 'tipo' => 'transferencia', 'ativo' => true],
        ];

        foreach ($formas as $forma) {
            FormaPagamento::updateOrCreate(
                ['nome' => $forma['nome']],
                $forma
            );
        }
    }
}
