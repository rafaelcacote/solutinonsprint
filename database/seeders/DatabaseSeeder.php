<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FormasPagamentoSeeder::class,
            CategoriasSeeder::class,
            CategoriasDespesaSeeder::class,
            ItensSeeder::class,
            RegrasPrecoSeeder::class,
            UsuarioAdminSeeder::class,
        ]);
    }
}
