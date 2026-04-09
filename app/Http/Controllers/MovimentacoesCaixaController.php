<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\FormaPagamento;
use App\Models\MovimentacaoCaixa;
use App\Models\Venda;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CRUD manual de movimentações de caixa.
 * Lançamentos automáticos a partir de {@see Venda} ou {@see Despesa} podem ser adicionados depois.
 */
class MovimentacoesCaixaController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $totalEntradas = (clone $query)
            ->where('tipo', MovimentacaoCaixa::TIPO_ENTRADA)
            ->sum('valor');

        $totalSaidas = (clone $query)
            ->where('tipo', MovimentacaoCaixa::TIPO_SAIDA)
            ->sum('valor');

        $saldo = round((float) $totalEntradas - (float) $totalSaidas, 2);

        $movimentacoes = (clone $query)
            ->orderByDesc('data_movimentacao')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $formasFiltro = FormaPagamento::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome']);

        return view('pages.movimentacoes-caixa.index', [
            'title' => 'Movimentações de caixa',
            'movimentacoes' => $movimentacoes,
            'totalEntradas' => $totalEntradas,
            'totalSaidas' => $totalSaidas,
            'saldo' => $saldo,
            'search' => $request->string('search')->trim()->toString(),
            'tipoFiltro' => $request->string('tipo')->trim()->toString(),
            'origemFiltro' => $request->string('origem')->trim()->toString(),
            'formaPagamentoFiltro' => $request->string('forma_pagamento_id')->trim()->toString(),
            'dataInicio' => $request->string('data_inicio')->trim()->toString(),
            'dataFim' => $request->string('data_fim')->trim()->toString(),
            'formasFiltro' => $formasFiltro,
        ]);
    }

    public function create()
    {
        return view('pages.movimentacoes-caixa.create', [
            'title' => 'Nova movimentação',
            'formasPagamento' => FormaPagamento::query()->where('ativo', true)->orderBy('nome')->get(),
            'vendas' => Venda::query()->orderByDesc('id')->get(['id', 'numero', 'total']),
            'despesas' => Despesa::query()->orderByDesc('id')->get(['id', 'descricao']),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeMovimentacaoRequest($request);
        $data = $this->validated($request, null);

        MovimentacaoCaixa::create([
            'tipo' => $data['tipo'],
            'origem' => $data['origem'],
            'descricao' => $data['descricao'],
            'valor' => round((float) $data['valor'], 2),
            'forma_pagamento_id' => $data['forma_pagamento_id'] ?? null,
            'venda_id' => $data['venda_id'] ?? null,
            'despesa_id' => $data['despesa_id'] ?? null,
            'data_movimentacao' => $data['data_movimentacao'],
        ]);

        return redirect()
            ->route('movimentacoes-caixa.index')
            ->with('success', 'Movimentação criada com sucesso.');
    }

    public function show(MovimentacaoCaixa $movimentacoes_caixa)
    {
        $movimentacoes_caixa->load(['formaPagamento', 'venda', 'despesa']);

        return view('pages.movimentacoes-caixa.show', [
            'title' => 'Movimentação — '.$movimentacoes_caixa->descricao,
            'movimentacao' => $movimentacoes_caixa,
        ]);
    }

    public function edit(MovimentacaoCaixa $movimentacoes_caixa)
    {
        $formasPagamento = FormaPagamento::query()
            ->where(function ($q) use ($movimentacoes_caixa) {
                $q->where('ativo', true);
                if ($movimentacoes_caixa->forma_pagamento_id !== null) {
                    $q->orWhere('id', $movimentacoes_caixa->forma_pagamento_id);
                }
            })
            ->orderBy('nome')
            ->get();

        return view('pages.movimentacoes-caixa.edit', [
            'title' => 'Editar movimentação',
            'movimentacao' => $movimentacoes_caixa,
            'formasPagamento' => $formasPagamento,
            'vendas' => Venda::query()->orderByDesc('id')->get(['id', 'numero', 'total']),
            'despesas' => Despesa::query()->orderByDesc('id')->get(['id', 'descricao']),
        ]);
    }

    public function update(Request $request, MovimentacaoCaixa $movimentacoes_caixa)
    {
        $this->normalizeMovimentacaoRequest($request);
        $data = $this->validated($request, $movimentacoes_caixa);

        $movimentacoes_caixa->tipo = $data['tipo'];
        $movimentacoes_caixa->origem = $data['origem'];
        $movimentacoes_caixa->descricao = $data['descricao'];
        $movimentacoes_caixa->valor = round((float) $data['valor'], 2);
        $movimentacoes_caixa->forma_pagamento_id = $data['forma_pagamento_id'] ?? null;
        $movimentacoes_caixa->venda_id = $data['venda_id'] ?? null;
        $movimentacoes_caixa->despesa_id = $data['despesa_id'] ?? null;
        $movimentacoes_caixa->data_movimentacao = $data['data_movimentacao'];
        $movimentacoes_caixa->save();

        return redirect()
            ->route('movimentacoes-caixa.index')
            ->with('success', 'Movimentação atualizada com sucesso.');
    }

    public function destroy(MovimentacaoCaixa $movimentacoes_caixa)
    {
        $movimentacoes_caixa->delete();

        return redirect()
            ->route('movimentacoes-caixa.index')
            ->with('success', 'Movimentação removida com sucesso.');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = MovimentacaoCaixa::query()
            ->with(['formaPagamento', 'venda', 'despesa']);

        $search = $request->string('search')->trim();
        if ($request->filled('search')) {
            $s = (string) $search;
            $query->where('descricao', 'like', "%{$s}%");
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo')->toString());
        }

        if ($request->filled('origem')) {
            $query->where('origem', $request->string('origem')->toString());
        }

        if ($request->filled('forma_pagamento_id')) {
            $query->where('forma_pagamento_id', (int) $request->input('forma_pagamento_id'));
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_movimentacao', '>=', $request->string('data_inicio')->toString());
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_movimentacao', '<=', $request->string('data_fim')->toString());
        }

        return $query;
    }

    private function normalizeMovimentacaoRequest(Request $request): void
    {
        if ($request->input('forma_pagamento_id') === '' || $request->input('forma_pagamento_id') === null) {
            $request->merge(['forma_pagamento_id' => null]);
        }

        if ($request->input('venda_id') === '' || $request->input('venda_id') === null) {
            $request->merge(['venda_id' => null]);
        }

        if ($request->input('despesa_id') === '' || $request->input('despesa_id') === null) {
            $request->merge(['despesa_id' => null]);
        }

        if (is_string($request->input('valor'))) {
            $request->merge(['valor' => str_replace(',', '.', $request->input('valor'))]);
        }
    }

    private function validated(Request $request, ?MovimentacaoCaixa $atual): array
    {
        $formaRule = ['nullable', 'integer'];
        if ($atual === null) {
            $formaRule[] = Rule::exists('formas_pagamento', 'id')->where('ativo', true);
        } else {
            $formaRule[] = Rule::exists('formas_pagamento', 'id')->where(function ($q) use ($atual) {
                $q->where('ativo', true);
                if ($atual->forma_pagamento_id !== null) {
                    $q->orWhere('id', $atual->forma_pagamento_id);
                }
            });
        }

        return $request->validate(
            [
                'tipo' => ['required', Rule::in([MovimentacaoCaixa::TIPO_ENTRADA, MovimentacaoCaixa::TIPO_SAIDA])],
                'origem' => ['required', Rule::in([
                    MovimentacaoCaixa::ORIGEM_VENDA,
                    MovimentacaoCaixa::ORIGEM_DESPESA,
                    MovimentacaoCaixa::ORIGEM_AJUSTE,
                    MovimentacaoCaixa::ORIGEM_RETIRADA,
                    MovimentacaoCaixa::ORIGEM_APORTE,
                ])],
                'descricao' => ['required', 'string', 'max:255'],
                'valor' => ['required', 'numeric', 'gt:0'],
                'forma_pagamento_id' => $formaRule,
                'venda_id' => ['nullable', 'integer', 'exists:vendas,id'],
                'despesa_id' => ['nullable', 'integer', 'exists:despesas,id'],
                'data_movimentacao' => ['required', 'date'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'numeric' => 'O campo :attribute deve ser um número válido.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'gt' => 'O campo :attribute deve ser maior que :value.',
            'max' => 'O campo :attribute não pode ser maior que :max.',
            'date' => 'Informe uma data válida.',
            'exists' => 'O :attribute selecionado é inválido.',
            'in' => 'O valor selecionado para :attribute é inválido.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'tipo' => 'tipo',
            'origem' => 'origem',
            'descricao' => 'descrição',
            'valor' => 'valor',
            'forma_pagamento_id' => 'forma de pagamento',
            'venda_id' => 'venda',
            'despesa_id' => 'despesa',
            'data_movimentacao' => 'data da movimentação',
        ];
    }
}
