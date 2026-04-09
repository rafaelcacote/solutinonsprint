<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query()->orderByDesc('id');

        $search = $request->string('search')->trim();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('telefone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$search}%");
            });
        }

        $clientes = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.clientes.index', [
            'title' => 'Clientes',
            'clientes' => $clientes,
            'search' => $search->toString(),
        ]);
    }

    /**
     * Busca JSON de clientes para autocomplete (orçamentos, etc.).
     */
    public function busca(Request $request)
    {
        $q = $request->string('q')->trim()->toString();
        if ($q === '') {
            return response()->json([]);
        }

        $clientes = Cliente::query()
            ->where(function ($query) use ($q) {
                $query->where('nome', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('telefone', 'like', "%{$q}%")
                    ->orWhere('cpf_cnpj', 'like', "%{$q}%");
            })
            ->orderBy('nome')
            ->limit(20)
            ->get(['id', 'nome', 'email', 'telefone']);

        return response()->json($clientes);
    }

    public function create()
    {
        return view('pages.clientes.create', [
            'title' => 'Novo Cliente',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'telefone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'cpf_cnpj' => ['nullable', 'string', 'max:20'],
                'observacoes' => ['nullable', 'string'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        Cliente::create([
            'nome' => $data['nome'],
            'telefone' => $data['telefone'] ?? null,
            'email' => $data['email'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente criado com sucesso.');
    }

    public function edit(Cliente $cliente)
    {
        return view('pages.clientes.edit', [
            'title' => 'Editar Cliente',
            'cliente' => $cliente,
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'telefone' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'cpf_cnpj' => ['nullable', 'string', 'max:20'],
                'observacoes' => ['nullable', 'string'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $cliente->nome = $data['nome'];
        $cliente->telefone = $data['telefone'] ?? null;
        $cliente->email = $data['email'] ?? null;
        $cliente->cpf_cnpj = $data['cpf_cnpj'] ?? null;
        $cliente->observacoes = $data['observacoes'] ?? null;
        $cliente->save();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente removido com sucesso.');
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
            'cpf_cnpj' => 'CPF/CNPJ',
            'observacoes' => 'observações',
        ];
    }
}
