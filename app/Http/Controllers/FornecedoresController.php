<?php

namespace App\Http\Controllers;

use App\Models\Fornecedor;
use Illuminate\Http\Request;

class FornecedoresController extends Controller
{
    public function index(Request $request)
    {
        $query = Fornecedor::query()->orderByDesc('id');

        $search = $request->string('search')->trim();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('telefone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contato', 'like', "%{$search}%");
            });
        }

        $fornecedores = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.fornecedores.index', [
            'title' => 'Fornecedores',
            'fornecedores' => $fornecedores,
            'search' => $search->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.fornecedores.create', [
            'title' => 'Novo Fornecedor',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'telefone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'contato' => ['nullable', 'string', 'max:255'],
                'observacoes' => ['nullable', 'string'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        Fornecedor::create([
            'nome' => $data['nome'],
            'telefone' => $data['telefone'] ?? null,
            'email' => $data['email'] ?? null,
            'contato' => $data['contato'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
        ]);

        return redirect()
            ->route('fornecedores.index')
            ->with('success', 'Fornecedor criado com sucesso.');
    }

    public function edit(Fornecedor $fornecedore)
    {
        return view('pages.fornecedores.edit', [
            'title' => 'Editar Fornecedor',
            'fornecedor' => $fornecedore,
        ]);
    }

    public function update(Request $request, Fornecedor $fornecedore)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'telefone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'contato' => ['nullable', 'string', 'max:255'],
                'observacoes' => ['nullable', 'string'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $fornecedore->nome = $data['nome'];
        $fornecedore->telefone = $data['telefone'] ?? null;
        $fornecedore->email = $data['email'] ?? null;
        $fornecedore->contato = $data['contato'] ?? null;
        $fornecedore->observacoes = $data['observacoes'] ?? null;
        $fornecedore->save();

        return redirect()
            ->route('fornecedores.index')
            ->with('success', 'Fornecedor atualizado com sucesso.');
    }

    public function destroy(Fornecedor $fornecedore)
    {
        $fornecedore->delete();

        return redirect()
            ->route('fornecedores.index')
            ->with('success', 'Fornecedor removido com sucesso.');
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'email' => 'Informe um e-mail válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'nome' => 'nome',
            'telefone' => 'telefone',
            'email' => 'e-mail',
            'contato' => 'contato',
            'observacoes' => 'observações',
        ];
    }
}
