<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->enum('tipo', ['produto', 'servico']);
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('unidade_medida')->default('un');
            $table->enum('modo_preco', ['fixo', 'quantidade', 'metro', 'personalizado']);
            $table->decimal('preco_venda', 10, 2)->default(0);
            $table->decimal('custo_estimado', 10, 2)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itens');
    }
};
