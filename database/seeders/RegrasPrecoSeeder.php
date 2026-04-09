<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\RegraPreco;
use Illuminate\Database\Seeder;

class RegrasPrecoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regrasPorItem = [
            'Impressão PB A4' => [
                ['quantidade_minima' => 1, 'quantidade_maxima' => 9, 'valor_unitario' => 1.00],
                ['quantidade_minima' => 10, 'quantidade_maxima' => 49, 'valor_unitario' => 0.75],
                ['quantidade_minima' => 50, 'quantidade_maxima' => null, 'valor_unitario' => 0.55],
            ],
            'Impressão Colorida A4' => [
                ['quantidade_minima' => 1, 'quantidade_maxima' => 9, 'valor_unitario' => 2.50],
                ['quantidade_minima' => 10, 'quantidade_maxima' => 49, 'valor_unitario' => 1.75],
                ['quantidade_minima' => 50, 'quantidade_maxima' => null, 'valor_unitario' => 1.50],
            ],
        ];

        foreach ($regrasPorItem as $nomeItem => $regras) {
            $item = Item::where('nome', $nomeItem)->first();

            if (! $item) {
                continue;
            }

            foreach ($regras as $regra) {
                RegraPreco::updateOrCreate(
                    [
                        'item_id' => $item->id,
                        'quantidade_minima' => $regra['quantidade_minima'],
                    ],
                    [
                        'quantidade_maxima' => $regra['quantidade_maxima'],
                        'valor_unitario' => $regra['valor_unitario'],
                        'observacao' => null,
                    ]
                );
            }
        }
    }
}
