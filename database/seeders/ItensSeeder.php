<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itens = [
            [
                'categoria' => 'Impressão',
                'tipo' => 'servico',
                'nome' => 'Impressão PB A4',
                'modo_preco' => 'quantidade',
                'preco_venda' => 1.00,
            ],
            [
                'categoria' => 'Impressão',
                'tipo' => 'servico',
                'nome' => 'Impressão Colorida A4',
                'modo_preco' => 'quantidade',
                'preco_venda' => 2.50,
            ],
            [
                'categoria' => 'Impressão',
                'tipo' => 'servico',
                'nome' => 'Impressão Couchê',
                'modo_preco' => 'quantidade',
                'preco_venda' => 8.00,
            ],
            [
                'categoria' => 'Impressão',
                'tipo' => 'servico',
                'nome' => 'Impressão Fotográfica',
                'modo_preco' => 'quantidade',
                'preco_venda' => 10.00,
            ],
            [
                'categoria' => 'Encadernação',
                'tipo' => 'servico',
                'nome' => 'Encadernação simples',
                'modo_preco' => 'quantidade',
                'preco_venda' => 7.00,
            ],
            [
                'categoria' => 'Plastificação',
                'tipo' => 'servico',
                'nome' => 'Plastificação',
                'modo_preco' => 'fixo',
                'preco_venda' => 5.00,
            ],
            [
                'categoria' => 'Scanner',
                'tipo' => 'servico',
                'nome' => 'Digitalização',
                'modo_preco' => 'fixo',
                'preco_venda' => 2.00,
            ],
            [
                'categoria' => 'Papelaria',
                'tipo' => 'produto',
                'nome' => 'Papel fotográfico avulso',
                'modo_preco' => 'fixo',
                'preco_venda' => 0.00,
            ],
            [
                'categoria' => 'Papelaria',
                'tipo' => 'produto',
                'nome' => 'Envelope',
                'modo_preco' => 'fixo',
                'preco_venda' => 0.00,
            ],
            [
                'categoria' => 'Papelaria',
                'tipo' => 'produto',
                'nome' => 'Papel A4 pacote',
                'modo_preco' => 'fixo',
                'preco_venda' => 0.00,
            ],
            [
                'categoria' => 'Personalizados',
                'tipo' => 'produto',
                'nome' => 'Caneca personalizada',
                'modo_preco' => 'personalizado',
                'preco_venda' => 0.00,
            ],
            [
                'categoria' => 'Personalizados',
                'tipo' => 'produto',
                'nome' => 'Copo personalizado',
                'modo_preco' => 'personalizado',
                'preco_venda' => 0.00,
            ],
            [
                'categoria' => 'Adesivos',
                'tipo' => 'servico',
                'nome' => 'Adesivo vinil',
                'modo_preco' => 'metro',
                'preco_venda' => 75.00,
            ],
            [
                'categoria' => 'Lona',
                'tipo' => 'servico',
                'nome' => 'Lona impressão',
                'modo_preco' => 'metro',
                'preco_venda' => 75.00,
            ],
        ];

        foreach ($itens as $item) {
            $categoria = Categoria::where('nome', $item['categoria'])->first();

            if (! $categoria) {
                continue;
            }

            Item::updateOrCreate(
                ['nome' => $item['nome']],
                [
                    'categoria_id' => $categoria->id,
                    'tipo' => $item['tipo'],
                    'modo_preco' => $item['modo_preco'],
                    'preco_venda' => $item['preco_venda'],
                    'unidade_medida' => 'un',
                    'ativo' => true,
                ]
            );
        }
    }
}
