<?php

namespace Database\Seeders;

use App\Models\CategoriaDespesa;
use Illuminate\Database\Seeder;

class CategoriasDespesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Aluguel',
            'Luz',
            'Internet',
            'Insumos',
            'Manutenção',
            'Retirada',
            'Outros',
        ];

        foreach ($categorias as $nome) {
            CategoriaDespesa::updateOrCreate(
                ['nome' => $nome],
                ['ativo' => true]
            );
        }
    }
}
