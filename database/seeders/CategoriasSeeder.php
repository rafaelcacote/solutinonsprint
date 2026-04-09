<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Impressão',
            'Encadernação',
            'Plastificação',
            'Scanner',
            'Papelaria',
            'Personalizados',
            'Lona',
            'Adesivos',
            'Outros',
        ];

        foreach ($categorias as $nome) {
            Categoria::updateOrCreate(
                ['nome' => $nome],
                ['ativo' => true]
            );
        }
    }
}
