<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriasController extends Controller
{
    public function index(Request $request)
    {
        $query = Categoria::query()->orderByDesc('id');

        $search = $request->string('search')->trim();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        $categorias = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.categorias.index', [
            'title' => 'Categorias',
            'categorias' => $categorias,
            'search' => $search->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.categorias.create', [
            'title' => 'Nova Categoria',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255', 'unique:categorias,nome'],
                'descricao' => ['nullable', 'string'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        Categoria::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'ativo' => $request->boolean('ativo', true),
        ]);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(Categoria $categoria)
    {
        return view('pages.categorias.edit', [
            'title' => 'Editar Categoria',
            'categoria' => $categoria,
        ]);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255', 'unique:categorias,nome,'.$categoria->id],
                'descricao' => ['nullable', 'string'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $categoria->nome = $data['nome'];
        $categoria->descricao = $data['descricao'] ?? null;
        $categoria->ativo = $request->boolean('ativo');
        $categoria->save();

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }

    public function toggleStatus(Request $request, Categoria $categoria)
    {
        $data = $request->validate(
            [
                'ativo' => ['required', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $categoria->ativo = (bool) $data['ativo'];
        $categoria->save();

        $mensagem = $categoria->ativo
            ? 'Categoria ativada com sucesso.'
            : 'Categoria desativada com sucesso.';

        return redirect()
            ->route('categorias.index', $request->only('search'))
            ->with('success', $mensagem);
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'unique' => 'Este :attribute já está em uso.',
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
