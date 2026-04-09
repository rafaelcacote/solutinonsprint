<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDespesa;
use Illuminate\Http\Request;

class CategoriasDespesaController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoriaDespesa::query()->orderByDesc('id');

        $search = $request->string('search')->trim();
        $status = $request->string('status')->trim()->toString();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $status === 'ativo');
        }

        $categoriasDespesa = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.categorias_despesa.index', [
            'title' => 'Categorias de despesa',
            'categoriasDespesa' => $categoriasDespesa,
            'search' => $search->toString(),
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('pages.categorias_despesa.create', [
            'title' => 'Nova categoria de despesa',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'descricao' => ['nullable', 'string'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        CategoriaDespesa::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'ativo' => $request->boolean('ativo', true),
        ]);

        return redirect()
            ->route('categorias-despesa.index')
            ->with('success', 'Categoria de despesa criada com sucesso.');
    }

    public function edit(CategoriaDespesa $categorias_despesa)
    {
        return view('pages.categorias_despesa.edit', [
            'title' => 'Editar categoria de despesa',
            'categoriaDespesa' => $categorias_despesa,
        ]);
    }

    public function update(Request $request, CategoriaDespesa $categorias_despesa)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'descricao' => ['nullable', 'string'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $categorias_despesa->nome = $data['nome'];
        $categorias_despesa->descricao = $data['descricao'] ?? null;
        $categorias_despesa->ativo = $request->boolean('ativo');
        $categorias_despesa->save();

        return redirect()
            ->route('categorias-despesa.index')
            ->with('success', 'Categoria de despesa atualizada com sucesso.');
    }

    public function destroy(CategoriaDespesa $categorias_despesa)
    {
        $categorias_despesa->delete();

        return redirect()
            ->route('categorias-despesa.index')
            ->with('success', 'Categoria de despesa removida com sucesso.');
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'nome' => 'nome',
            'descricao' => 'descrição',
            'ativo' => 'status',
        ];
    }
}
