<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\FormaPagamento;
use App\Models\Item;
use App\Models\MovimentacaoCaixa;
use App\Models\Orcamento;
use App\Models\Venda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * CRUD de vendas (cabeçalho, itens e recebimentos).
 * Integração com {@see MovimentacaoCaixa} pode ser acoplada aqui no futuro (ex.: após store/update).
 */
class VendasController extends Controller
{
    public function index(Request $request)
    {
        $query = Venda::query()
            ->with(['cliente'])
            ->withSum('recebimentos', 'valor')
            ->orderByDesc('data_venda')
            ->orderByDesc('id');

        $search = $request->string('search')->trim();
        $statusFiltro = $request->string('status')->trim()->toString();
        $dataInicio = $request->string('data_inicio')->trim()->toString();
        $dataFim = $request->string('data_fim')->trim()->toString();

        if ($request->filled('search')) {
            $s = (string) $search;
            $query->where(function ($q) use ($s) {
                $q->where('numero', 'like', "%{$s}%")
                    ->orWhereHas('cliente', function ($cq) use ($s) {
                        $cq->where('nome', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $statusFiltro);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_venda', '>=', $dataInicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_venda', '<=', $dataFim);
        }

        $vendas = $query
            ->paginate(10)
            ->withQueryString();

        return view('pages.vendas.index', [
            'title' => 'Vendas',
            'vendas' => $vendas,
            'search' => $search->toString(),
            'statusFiltro' => $statusFiltro,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
        ]);
    }

    /**
     * JSON para preencher o formulário de venda a partir de um orçamento (autocomplete no front).
     */
    public function orcamentoParaVenda(Orcamento $orcamento): JsonResponse
    {
        if (in_array($orcamento->status, [Orcamento::STATUS_CONVERTIDO, Orcamento::STATUS_RECUSADO], true)) {
            return response()->json(['message' => 'Este orçamento não pode ser usado para gerar venda.'], 422);
        }

        $orcamento->load(['itens.item', 'cliente']);

        $itens = $orcamento->itens->map(function ($li) {
            $item = $li->item;

            return [
                'item_id' => $li->item_id ? (string) $li->item_id : '',
                'categoria_id' => $item?->categoria_id ? (string) $item->categoria_id : '',
                'descricao_item' => $li->descricao_item,
                'quantidade' => (string) $li->quantidade,
                'unidade_medida' => $item?->unidade_medida ?? 'un',
                'valor_unitario' => (string) $li->valor_unitario,
                'custo_unitario' => $item && $item->custo_estimado !== null ? (string) $item->custo_estimado : '',
                'largura' => $li->largura !== null ? (string) $li->largura : '',
                'altura' => $li->altura !== null ? (string) $li->altura : '',
                'metragem' => $li->metragem !== null ? (string) $li->metragem : '',
                'observacoes' => $li->observacoes ?? '',
            ];
        })->values()->all();

        return response()->json([
            'orcamento_id' => $orcamento->id,
            'cliente_id' => $orcamento->cliente_id ? (string) $orcamento->cliente_id : '',
            'cliente_nome' => $orcamento->cliente?->nome ?? '',
            'desconto' => (string) $orcamento->desconto,
            'itens' => $itens,
        ]);
    }

    public function create()
    {
        $catalog = $this->catalogoItensParaVenda();
        $categorias = Categoria::query()->orderBy('nome')->get(['id', 'nome']);
        $formasPagamento = FormaPagamento::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'tipo']);
        $orcamentos = $this->orcamentosDisponiveisParaSelect();

        return view('pages.vendas.create', [
            'title' => 'Nova Venda',
            'clienteInicial' => $this->clienteInicialParaForm(),
            'catalog' => $catalog,
            'categorias' => $categorias,
            'formasPagamento' => $formasPagamento,
            'orcamentos' => $orcamentos,
            'defaultLines' => old('itens', [
                $this->linhaItemVazia(),
            ]),
            'defaultRecebimentos' => old('recebimentos', []),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeVendaRequest($request);
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $numero = $this->gerarProximoNumero();
            $linhas = $this->montarLinhasItensPersistencia($data['itens']);
            $subtotal = round(array_sum(array_column($linhas, 'subtotal')), 2);
            $desconto = round((float) ($data['desconto'] ?? 0), 2);
            $total = max(0, round($subtotal - $desconto, 2));

            $venda = Venda::create([
                'cliente_id' => $data['cliente_id'] ?? null,
                'orcamento_id' => $data['orcamento_id'] ?? null,
                'numero' => $numero,
                'data_venda' => $data['data_venda'],
                'status' => $data['status'],
                'subtotal' => $subtotal,
                'desconto' => $desconto,
                'total' => $total,
                'observacoes' => $data['observacoes'] ?? null,
            ]);

            foreach ($linhas as $linha) {
                $venda->itens()->create($linha);
            }

            foreach ($this->montarLinhasRecebimentosPersistencia($data['recebimentos'] ?? []) as $rec) {
                $venda->recebimentos()->create($rec);
            }

            $this->marcarOrcamentoConvertidoSeNecessario($data['orcamento_id'] ?? null);
        });

        return redirect()
            ->route('vendas.index')
            ->with('success', 'Venda criada com sucesso.');
    }

    public function show(Venda $venda)
    {
        $venda->load(['cliente', 'orcamento', 'itens.item', 'itens.categoria', 'recebimentos.formaPagamento']);

        return view('pages.vendas.show', [
            'title' => 'Venda '.$venda->numero,
            'venda' => $venda,
        ]);
    }

    public function edit(Venda $venda)
    {
        $venda->load(['itens', 'recebimentos']);
        $catalog = $this->catalogoItensParaVenda($venda);
        $categorias = Categoria::query()->orderBy('nome')->get(['id', 'nome']);
        $formasPagamento = FormaPagamento::query()->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'tipo']);
        $orcamentos = $this->orcamentosDisponiveisParaSelect($venda);

        $persistedLines = $venda->itens->map(function ($li) {
            return [
                'item_id' => $li->item_id ? (string) $li->item_id : '',
                'categoria_id' => $li->categoria_id ? (string) $li->categoria_id : '',
                'descricao_item' => $li->descricao_item,
                'quantidade' => (string) $li->quantidade,
                'unidade_medida' => $li->unidade_medida,
                'valor_unitario' => (string) $li->valor_unitario,
                'custo_unitario' => $li->custo_unitario !== null ? (string) $li->custo_unitario : '',
                'largura' => $li->largura !== null ? (string) $li->largura : '',
                'altura' => $li->altura !== null ? (string) $li->altura : '',
                'metragem' => $li->metragem !== null ? (string) $li->metragem : '',
                'observacoes' => $li->observacoes ?? '',
            ];
        })->values()->all();

        $persistedRec = $venda->recebimentos->map(function ($r) {
            return [
                'forma_pagamento_id' => (string) $r->forma_pagamento_id,
                'valor' => (string) $r->valor,
                'data_recebimento' => $r->data_recebimento->format('Y-m-d\TH:i'),
                'observacoes' => $r->observacoes ?? '',
            ];
        })->values()->all();

        return view('pages.vendas.edit', [
            'title' => 'Editar Venda',
            'venda' => $venda,
            'clienteInicial' => $this->clienteInicialParaForm($venda),
            'catalog' => $catalog,
            'categorias' => $categorias,
            'formasPagamento' => $formasPagamento,
            'orcamentos' => $orcamentos,
            'defaultLines' => old('itens', $persistedLines !== [] ? $persistedLines : [$this->linhaItemVazia()]),
            'defaultRecebimentos' => old('recebimentos', $persistedRec),
        ]);
    }

    public function update(Request $request, Venda $venda)
    {
        $this->normalizeVendaRequest($request);
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $venda) {
            $linhas = $this->montarLinhasItensPersistencia($data['itens']);
            $subtotal = round(array_sum(array_column($linhas, 'subtotal')), 2);
            $desconto = round((float) ($data['desconto'] ?? 0), 2);
            $total = max(0, round($subtotal - $desconto, 2));

            $venda->cliente_id = $data['cliente_id'] ?? null;
            $venda->orcamento_id = $data['orcamento_id'] ?? null;
            $venda->data_venda = $data['data_venda'];
            $venda->status = $data['status'];
            $venda->subtotal = $subtotal;
            $venda->desconto = $desconto;
            $venda->total = $total;
            $venda->observacoes = $data['observacoes'] ?? null;
            $venda->save();

            $venda->itens()->delete();
            foreach ($linhas as $linha) {
                $venda->itens()->create($linha);
            }

            $venda->recebimentos()->delete();
            foreach ($this->montarLinhasRecebimentosPersistencia($data['recebimentos'] ?? []) as $rec) {
                $venda->recebimentos()->create($rec);
            }

            $this->marcarOrcamentoConvertidoSeNecessario($data['orcamento_id'] ?? null);
        });

        return redirect()
            ->route('vendas.index')
            ->with('success', 'Venda atualizada com sucesso.');
    }

    public function destroy(Venda $venda)
    {
        $venda->delete();

        return redirect()
            ->route('vendas.index')
            ->with('success', 'Venda removida com sucesso.');
    }

    /**
     * @return array{id: int, nome: string}|null
     */
    private function clienteInicialParaForm(?Venda $venda = null): ?array
    {
        $cid = old('cliente_id', $venda?->cliente_id);
        if ($cid === null || $cid === '') {
            return null;
        }

        $cliente = Cliente::query()->find((int) $cid);
        if ($cliente === null) {
            return null;
        }

        return [
            'id' => $cliente->id,
            'nome' => $cliente->nome,
        ];
    }

    /**
     * Orçamentos que ainda podem originar venda (inclui o vínculo atual na edição).
     *
     * @return Collection<int, Orcamento>
     */
    private function orcamentosDisponiveisParaSelect(?Venda $vendaAtual = null)
    {
        $q = Orcamento::query()
            ->with('cliente')
            ->whereNotIn('status', [Orcamento::STATUS_CONVERTIDO, Orcamento::STATUS_RECUSADO])
            ->orderByDesc('data_orcamento')
            ->limit(80);

        $list = $q->get();

        if ($vendaAtual?->orcamento_id) {
            $atual = Orcamento::query()->with('cliente')->find($vendaAtual->orcamento_id);
            if ($atual && ! $list->pluck('id')->contains($atual->id)) {
                $list = $list->prepend($atual)->unique('id')->values();
            }
        }

        return $list;
    }

    private function marcarOrcamentoConvertidoSeNecessario(?int $orcamentoId): void
    {
        if ($orcamentoId === null) {
            return;
        }

        Orcamento::query()->whereKey($orcamentoId)->update(['status' => Orcamento::STATUS_CONVERTIDO]);
    }

    /**
     * @return array<string, mixed>
     */
    private function linhaItemVazia(): array
    {
        return [
            'item_id' => '',
            'categoria_id' => '',
            'descricao_item' => '',
            'quantidade' => '1',
            'unidade_medida' => 'un',
            'valor_unitario' => '0',
            'custo_unitario' => '',
            'largura' => '',
            'altura' => '',
            'metragem' => '',
            'observacoes' => '',
        ];
    }

    /**
     * @return Collection<int, array{id: int, nome: string, modo_preco: string, preco_venda: string, unidade_medida: string, categoria_id: int|null, custo_estimado: string|null}>
     */
    private function catalogoItensParaVenda(?Venda $venda = null)
    {
        $ativos = Item::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'modo_preco', 'preco_venda', 'unidade_medida', 'categoria_id', 'custo_estimado']);

        if ($venda === null) {
            return $ativos->map(fn (Item $i) => [
                'id' => $i->id,
                'nome' => $i->nome,
                'modo_preco' => $i->modo_preco,
                'preco_venda' => (string) $i->preco_venda,
                'unidade_medida' => $i->unidade_medida ?? 'un',
                'categoria_id' => $i->categoria_id,
                'custo_estimado' => $i->custo_estimado !== null ? (string) $i->custo_estimado : null,
            ])->values();
        }

        $idsExtras = $venda->itens->pluck('item_id')->filter()->unique()->values();
        $extras = $idsExtras->isEmpty()
            ? collect()
            : Item::query()->whereIn('id', $idsExtras)->get(['id', 'nome', 'modo_preco', 'preco_venda', 'unidade_medida', 'categoria_id', 'custo_estimado']);

        return $ativos
            ->concat($extras)
            ->unique('id')
            ->sortBy('nome', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(fn (Item $i) => [
                'id' => $i->id,
                'nome' => $i->nome,
                'modo_preco' => $i->modo_preco,
                'preco_venda' => (string) $i->preco_venda,
                'unidade_medida' => $i->unidade_medida ?? 'un',
                'categoria_id' => $i->categoria_id,
                'custo_estimado' => $i->custo_estimado !== null ? (string) $i->custo_estimado : null,
            ])
            ->values();
    }

    /**
     * @param  array<int, array<string, mixed>>  $itens
     * @return array<int, array<string, mixed>>
     */
    private function montarLinhasItensPersistencia(array $itens): array
    {
        $out = [];
        foreach ($itens as $row) {
            $q = round((float) $row['quantidade'], 2);
            $vu = round((float) $row['valor_unitario'], 2);
            $sub = round($q * $vu, 2);

            $cu = $this->optionalDecimal($row['custo_unitario'] ?? null);
            $ct = $cu !== null ? round($q * $cu, 2) : null;

            $out[] = [
                'item_id' => ! empty($row['item_id']) ? (int) $row['item_id'] : null,
                'categoria_id' => ! empty($row['categoria_id']) ? (int) $row['categoria_id'] : null,
                'descricao_item' => $row['descricao_item'],
                'quantidade' => $q,
                'unidade_medida' => $row['unidade_medida'],
                'valor_unitario' => $vu,
                'subtotal' => $sub,
                'custo_unitario' => $cu,
                'custo_total' => $ct,
                'largura' => $this->optionalDecimal($row['largura'] ?? null),
                'altura' => $this->optionalDecimal($row['altura'] ?? null),
                'metragem' => $this->optionalDecimal($row['metragem'] ?? null),
                'observacoes' => isset($row['observacoes']) && $row['observacoes'] !== '' ? $row['observacoes'] : null,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $recebimentos
     * @return array<int, array<string, mixed>>
     */
    private function montarLinhasRecebimentosPersistencia(array $recebimentos): array
    {
        $out = [];
        foreach ($recebimentos as $row) {
            $out[] = [
                'forma_pagamento_id' => (int) $row['forma_pagamento_id'],
                'valor' => round((float) $row['valor'], 2),
                'data_recebimento' => $row['data_recebimento'],
                'observacoes' => isset($row['observacoes']) && $row['observacoes'] !== '' ? $row['observacoes'] : null,
            ];
        }

        return $out;
    }

    private function optionalDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    private function gerarProximoNumero(): string
    {
        $year = (int) now()->year;
        $prefix = sprintf('VEN-%d-', $year);
        $last = Venda::query()
            ->where('numero', 'like', $prefix.'%')
            ->orderByDesc('numero')
            ->lockForUpdate()
            ->first();

        if ($last === null) {
            return $prefix.str_pad('1', 5, '0', STR_PAD_LEFT);
        }

        $suffix = substr($last->numero, strlen($prefix));
        $next = (int) $suffix + 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function normalizeVendaRequest(Request $request): void
    {
        if ($request->input('cliente_id') === '' || $request->input('cliente_id') === null) {
            $request->merge(['cliente_id' => null]);
        }

        if ($request->input('orcamento_id') === '' || $request->input('orcamento_id') === null) {
            $request->merge(['orcamento_id' => null]);
        }

        if ($request->input('desconto') === '' || $request->input('desconto') === null) {
            $request->merge(['desconto' => '0']);
        } elseif (is_string($request->input('desconto'))) {
            $request->merge(['desconto' => str_replace(',', '.', $request->input('desconto'))]);
        }

        $itens = $request->input('itens', []);
        if (is_array($itens)) {
            $normalized = [];
            foreach ($itens as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $itemId = $row['item_id'] ?? null;
                if ($itemId === '' || $itemId === null) {
                    $row['item_id'] = null;
                } else {
                    $row['item_id'] = (int) $itemId;
                }

                $catId = $row['categoria_id'] ?? null;
                if ($catId === '' || $catId === null) {
                    $row['categoria_id'] = null;
                } else {
                    $row['categoria_id'] = (int) $catId;
                }

                foreach (['quantidade', 'valor_unitario', 'custo_unitario', 'largura', 'altura', 'metragem'] as $campo) {
                    if (isset($row[$campo]) && is_string($row[$campo])) {
                        $row[$campo] = str_replace(',', '.', $row[$campo]);
                    }
                }

                $normalized[] = $row;
            }
            $request->merge(['itens' => $normalized]);
        }

        $rec = $request->input('recebimentos', []);
        if (! is_array($rec)) {
            $request->merge(['recebimentos' => []]);

            return;
        }

        $recOut = [];
        foreach ($rec as $row) {
            if (! is_array($row)) {
                continue;
            }
            $fp = $row['forma_pagamento_id'] ?? null;
            $valor = $row['valor'] ?? null;
            if (($fp === '' || $fp === null) && ($valor === '' || $valor === null || $valor === '0')) {
                continue;
            }
            if (isset($row['valor']) && is_string($row['valor'])) {
                $row['valor'] = str_replace(',', '.', $row['valor']);
            }
            $recOut[] = $row;
        }
        $request->merge(['recebimentos' => $recOut]);
    }

    private function validated(Request $request): array
    {
        return $request->validate(
            [
                'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
                'orcamento_id' => ['nullable', 'integer', 'exists:orcamentos,id'],
                'data_venda' => ['required', 'date'],
                'status' => ['required', 'in:aberta,concluida,cancelada'],
                'desconto' => ['nullable', 'numeric', 'min:0'],
                'observacoes' => ['nullable', 'string'],
                'itens' => ['required', 'array', 'min:1'],
                'itens.*.item_id' => ['nullable', 'integer', 'exists:itens,id'],
                'itens.*.categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
                'itens.*.descricao_item' => ['required', 'string', 'max:255'],
                'itens.*.quantidade' => ['required', 'numeric', 'min:0.01'],
                'itens.*.unidade_medida' => ['required', 'string', 'max:30'],
                'itens.*.valor_unitario' => ['required', 'numeric', 'min:0'],
                'itens.*.custo_unitario' => ['nullable', 'numeric', 'min:0'],
                'itens.*.largura' => ['nullable', 'numeric', 'min:0'],
                'itens.*.altura' => ['nullable', 'numeric', 'min:0'],
                'itens.*.metragem' => ['nullable', 'numeric', 'min:0'],
                'itens.*.observacoes' => ['nullable', 'string'],
                'recebimentos' => ['nullable', 'array'],
                'recebimentos.*.forma_pagamento_id' => ['required', 'integer', Rule::exists('formas_pagamento', 'id')->where('ativo', true)],
                'recebimentos.*.valor' => ['required', 'numeric', 'min:0.01'],
                'recebimentos.*.data_recebimento' => ['required', 'date'],
                'recebimentos.*.observacoes' => ['nullable', 'string'],
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
            'cliente_id' => 'cliente',
            'orcamento_id' => 'orçamento',
            'data_venda' => 'data da venda',
            'status' => 'status',
            'desconto' => 'desconto',
            'observacoes' => 'observações',
            'itens' => 'itens',
            'itens.*.item_id' => 'item cadastrado',
            'itens.*.categoria_id' => 'categoria',
            'itens.*.descricao_item' => 'descrição do item',
            'itens.*.quantidade' => 'quantidade',
            'itens.*.unidade_medida' => 'unidade de medida',
            'itens.*.valor_unitario' => 'valor unitário',
            'itens.*.custo_unitario' => 'custo unitário',
            'itens.*.largura' => 'largura',
            'itens.*.altura' => 'altura',
            'itens.*.metragem' => 'metragem',
            'itens.*.observacoes' => 'observações do item',
            'recebimentos' => 'recebimentos',
            'recebimentos.*.forma_pagamento_id' => 'forma de pagamento',
            'recebimentos.*.valor' => 'valor do recebimento',
            'recebimentos.*.data_recebimento' => 'data do recebimento',
            'recebimentos.*.observacoes' => 'observações do recebimento',
        ];
    }
}
