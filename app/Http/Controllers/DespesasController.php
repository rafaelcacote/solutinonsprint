<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDespesa;
use App\Models\Despesa;
use App\Models\FormaPagamento;
use App\Models\Fornecedor;
use App\Models\MovimentacaoCaixa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CRUD de despesas.
 * Integração com {@see MovimentacaoCaixa} pode ser adicionada posteriormente nos fluxos de store/update.
 */
class DespesasController extends Controller
{
    public function index(Request $request)
    {
        $query = Despesa::query()
            ->with(['categoriaDespesa', 'fornecedor', 'formaPagamento'])
            ->orderByDesc('data_vencimento')
            ->orderByDesc('id');

        $search = $request->string('search')->trim();
        if ($request->filled('search')) {
            $s = (string) $search;
            $query->where('descricao', 'like', "%{$s}%");
        }

        if ($request->filled('categoria_despesa_id')) {
            $query->where('categoria_despesa_id', (int) $request->input('categoria_despesa_id'));
        }

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', (int) $request->input('fornecedor_id'));
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->string('tipo')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('vencimento_inicio')) {
            $query->whereDate('data_vencimento', '>=', $request->string('vencimento_inicio')->toString());
        }

        if ($request->filled('vencimento_fim')) {
            $query->whereDate('data_vencimento', '<=', $request->string('vencimento_fim')->toString());
        }

        if ($request->filled('pagamento_inicio')) {
            $query->whereDate('data_pagamento', '>=', $request->string('pagamento_inicio')->toString());
        }

        if ($request->filled('pagamento_fim')) {
            $query->whereDate('data_pagamento', '<=', $request->string('pagamento_fim')->toString());
        }

        $despesas = $query
            ->paginate(10)
            ->withQueryString();

        $categoriasFiltro = CategoriaDespesa::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome']);
        $fornecedoresFiltro = Fornecedor::query()->orderBy('nome')->get(['id', 'nome']);

        return view('pages.despesas.index', [
            'title' => 'Despesas',
            'despesas' => $despesas,
            'search' => $search->toString(),
            'categoriaFiltro' => $request->string('categoria_despesa_id')->trim()->toString(),
            'fornecedorFiltro' => $request->string('fornecedor_id')->trim()->toString(),
            'tipoFiltro' => $request->string('tipo')->trim()->toString(),
            'statusFiltro' => $request->string('status')->trim()->toString(),
            'vencimentoInicio' => $request->string('vencimento_inicio')->trim()->toString(),
            'vencimentoFim' => $request->string('vencimento_fim')->trim()->toString(),
            'pagamentoInicio' => $request->string('pagamento_inicio')->trim()->toString(),
            'pagamentoFim' => $request->string('pagamento_fim')->trim()->toString(),
            'categoriasFiltro' => $categoriasFiltro,
            'fornecedoresFiltro' => $fornecedoresFiltro,
        ]);
    }

    public function create()
    {
        return view('pages.despesas.create', [
            'title' => 'Nova Despesa',
            'categorias' => CategoriaDespesa::query()->where('ativo', true)->orderBy('nome')->get(),
            'fornecedores' => Fornecedor::query()->orderBy('nome')->get(),
            'formasPagamento' => FormaPagamento::query()->where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeDespesaRequest($request);
        $data = $this->validated($request, null);

        Despesa::create([
            'categoria_despesa_id' => $data['categoria_despesa_id'],
            'fornecedor_id' => $data['fornecedor_id'] ?? null,
            'descricao' => $data['descricao'],
            'tipo' => $data['tipo'],
            'valor' => round((float) $data['valor'], 2),
            'data_vencimento' => $data['data_vencimento'] ?? null,
            'data_pagamento' => $data['data_pagamento'] ?? null,
            'status' => $data['status'],
            'forma_pagamento_id' => $data['forma_pagamento_id'] ?? null,
            'observacoes' => $data['observacoes'] ?? null,
        ]);

        return redirect()
            ->route('despesas.index')
            ->with('success', 'Despesa criada com sucesso.');
    }

    public function show(Despesa $despesa)
    {
        $despesa->load(['categoriaDespesa', 'fornecedor', 'formaPagamento']);

        return view('pages.despesas.show', [
            'title' => 'Despesa — '.$despesa->descricao,
            'despesa' => $despesa,
        ]);
    }

    public function edit(Despesa $despesa)
    {
        $categorias = CategoriaDespesa::query()
            ->where(function ($q) use ($despesa) {
                $q->where('ativo', true)->orWhere('id', $despesa->categoria_despesa_id);
            })
            ->orderBy('nome')
            ->get();

        $formasPagamento = FormaPagamento::query()
            ->where(function ($q) use ($despesa) {
                $q->where('ativo', true);
                if ($despesa->forma_pagamento_id !== null) {
                    $q->orWhere('id', $despesa->forma_pagamento_id);
                }
            })
            ->orderBy('nome')
            ->get();

        return view('pages.despesas.edit', [
            'title' => 'Editar Despesa',
            'despesa' => $despesa,
            'categorias' => $categorias,
            'fornecedores' => Fornecedor::query()->orderBy('nome')->get(),
            'formasPagamento' => $formasPagamento,
        ]);
    }

    public function update(Request $request, Despesa $despesa)
    {
        $this->normalizeDespesaRequest($request);
        $data = $this->validated($request, $despesa);

        $despesa->categoria_despesa_id = $data['categoria_despesa_id'];
        $despesa->fornecedor_id = $data['fornecedor_id'] ?? null;
        $despesa->descricao = $data['descricao'];
        $despesa->tipo = $data['tipo'];
        $despesa->valor = round((float) $data['valor'], 2);
        $despesa->data_vencimento = $data['data_vencimento'] ?? null;
        $despesa->data_pagamento = $data['data_pagamento'] ?? null;
        $despesa->status = $data['status'];
        $despesa->forma_pagamento_id = $data['forma_pagamento_id'] ?? null;
        $despesa->observacoes = $data['observacoes'] ?? null;
        $despesa->save();

        return redirect()
            ->route('despesas.index')
            ->with('success', 'Despesa atualizada com sucesso.');
    }

    public function destroy(Despesa $despesa)
    {
        $despesa->delete();

        return redirect()
            ->route('despesas.index')
            ->with('success', 'Despesa removida com sucesso.');
    }

    private function normalizeDespesaRequest(Request $request): void
    {
        if ($request->input('fornecedor_id') === '' || $request->input('fornecedor_id') === null) {
            $request->merge(['fornecedor_id' => null]);
        }

        if ($request->input('forma_pagamento_id') === '' || $request->input('forma_pagamento_id') === null) {
            $request->merge(['forma_pagamento_id' => null]);
        }

        if ($request->input('data_vencimento') === '') {
            $request->merge(['data_vencimento' => null]);
        }

        if ($request->input('data_pagamento') === '') {
            $request->merge(['data_pagamento' => null]);
        }

        if (is_string($request->input('valor'))) {
            $request->merge(['valor' => str_replace(',', '.', $request->input('valor'))]);
        }

        if ($request->filled('data_pagamento') && $request->input('status') === Despesa::STATUS_PENDENTE) {
            $request->merge(['status' => Despesa::STATUS_PAGO]);
        }
    }

    private function validated(Request $request, ?Despesa $despesaAtual): array
    {
        $categoriaRule = $despesaAtual === null
            ? ['required', 'integer', Rule::exists('categorias_despesa', 'id')->where('ativo', true)]
            : [
                'required',
                'integer',
                Rule::exists('categorias_despesa', 'id')->where(function ($q) use ($despesaAtual) {
                    $q->where('ativo', true)->orWhere('id', $despesaAtual->categoria_despesa_id);
                }),
            ];

        $formaRule = ['nullable', 'integer'];
        if ($despesaAtual === null) {
            $formaRule[] = Rule::exists('formas_pagamento', 'id')->where('ativo', true);
        } else {
            $formaRule[] = Rule::exists('formas_pagamento', 'id')->where(function ($q) use ($despesaAtual) {
                $q->where('ativo', true);
                if ($despesaAtual->forma_pagamento_id !== null) {
                    $q->orWhere('id', $despesaAtual->forma_pagamento_id);
                }
            });
        }

        return $request->validate(
            [
                'categoria_despesa_id' => $categoriaRule,
                'fornecedor_id' => ['nullable', 'integer', 'exists:fornecedores,id'],
                'descricao' => ['required', 'string', 'max:255'],
                'tipo' => ['required', Rule::in([Despesa::TIPO_FIXA, Despesa::TIPO_VARIAVEL])],
                'valor' => ['required', 'numeric', 'min:0'],
                'data_vencimento' => ['nullable', 'date'],
                'data_pagamento' => ['nullable', 'date', 'required_if:status,'.Despesa::STATUS_PAGO],
                'status' => ['required', Rule::in([Despesa::STATUS_PENDENTE, Despesa::STATUS_PAGO, Despesa::STATUS_VENCIDO])],
                'forma_pagamento_id' => $formaRule,
                'observacoes' => ['nullable', 'string'],
            ],
            $this->validationMessages(),
            $this->validationAttributes()
        );
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'required_if' => 'O campo :attribute é obrigatório quando :other é :value.',
            'string' => 'O campo :attribute deve ser um texto válido.',
            'numeric' => 'O campo :attribute deve ser um número válido.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute não pode ser maior que :max.',
            'date' => 'Informe uma data válida.',
            'exists' => 'O :attribute selecionado é inválido.',
            'in' => 'O valor selecionado para :attribute é inválido.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'categoria_despesa_id' => 'categoria de despesa',
            'fornecedor_id' => 'fornecedor',
            'descricao' => 'descrição',
            'tipo' => 'tipo',
            'valor' => 'valor',
            'data_vencimento' => 'data de vencimento',
            'data_pagamento' => 'data de pagamento',
            'status' => 'status',
            'forma_pagamento_id' => 'forma de pagamento',
            'observacoes' => 'observações',
        ];
    }
}
