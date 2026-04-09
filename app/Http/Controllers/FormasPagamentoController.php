<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use Illuminate\Http\Request;

class FormasPagamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = FormaPagamento::query()->orderByDesc('id');

        $search = $request->string('search')->trim();
        $tipoFiltro = $request->string('tipo')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        if ($request->filled('search')) {
            $query->where('nome', 'like', "%{$search}%");
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $tipoFiltro);
        }

        if ($request->filled('status')) {
            $query->where('ativo', $status === 'ativo');
        }

        $formasPagamento = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.formas_pagamento.index', [
            'title' => 'Formas de pagamento',
            'formasPagamento' => $formasPagamento,
            'search' => $search->toString(),
            'tipoFiltro' => $tipoFiltro,
            'status' => $status,
        ]);
    }

    public function create()
    {
        return view('pages.formas_pagamento.create', [
            'title' => 'Nova forma de pagamento',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'tipo' => ['required', 'in:dinheiro,pix,cartao,transferencia,outro'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        FormaPagamento::create([
            'nome' => $data['nome'],
            'tipo' => $data['tipo'],
            'ativo' => $request->boolean('ativo', true),
        ]);

        return redirect()
            ->route('formas-pagamento.index')
            ->with('success', 'Forma de pagamento criada com sucesso.');
    }

    public function edit(FormaPagamento $formas_pagamento)
    {
        return view('pages.formas_pagamento.edit', [
            'title' => 'Editar forma de pagamento',
            'formaPagamento' => $formas_pagamento,
        ]);
    }

    public function update(Request $request, FormaPagamento $formas_pagamento)
    {
        $data = $request->validate(
            [
                'nome' => ['required', 'string', 'max:255'],
                'tipo' => ['required', 'in:dinheiro,pix,cartao,transferencia,outro'],
                'ativo' => ['nullable', 'boolean'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $formas_pagamento->nome = $data['nome'];
        $formas_pagamento->tipo = $data['tipo'];
        $formas_pagamento->ativo = $request->boolean('ativo');
        $formas_pagamento->save();

        return redirect()
            ->route('formas-pagamento.index')
            ->with('success', 'Forma de pagamento atualizada com sucesso.');
    }

    public function destroy(FormaPagamento $formas_pagamento)
    {
        $formas_pagamento->delete();

        return redirect()
            ->route('formas-pagamento.index')
            ->with('success', 'Forma de pagamento removida com sucesso.');
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'in' => 'O valor selecionado para :attribute é inválido.',
            'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'nome' => 'nome',
            'tipo' => 'tipo',
            'ativo' => 'status',
        ];
    }
}
