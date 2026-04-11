<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Despesa;
use App\Models\Item;
use App\Models\MovimentacaoCaixa;
use App\Models\Orcamento;
use App\Models\Venda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Painel gerencial: indicadores do período (mês/ano), resumos e lançamentos recentes.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [$inicio, $fim, $mes, $ano] = $this->resolverPeriodo($request);
        $dataDia = $this->resolverDiaNoPeriodo($request, $inicio, $fim);
        $inicioDia = $dataDia->copy()->startOfDay();
        $fimDia = $dataDia->copy()->endOfDay();

        $totalVendasMes = (float) Venda::query()
            ->whereBetween('data_venda', [$inicio, $fim])
            ->sum('total');

        $totalDespesasMes = (float) $this->queryDespesasNoPeriodo($inicio, $fim)->sum('valor');

        $entradasCaixaMes = (float) MovimentacaoCaixa::query()
            ->where('tipo', MovimentacaoCaixa::TIPO_ENTRADA)
            ->whereBetween('data_movimentacao', [$inicio, $fim])
            ->sum('valor');

        $saidasCaixaMes = (float) MovimentacaoCaixa::query()
            ->where('tipo', MovimentacaoCaixa::TIPO_SAIDA)
            ->whereBetween('data_movimentacao', [$inicio, $fim])
            ->sum('valor');

        $saldoCaixaMes = round($entradasCaixaMes - $saidasCaixaMes, 2);

        $qtdVendasMes = (int) Venda::query()
            ->whereBetween('data_venda', [$inicio, $fim])
            ->count();

        $qtdOrcamentosMes = (int) Orcamento::query()
            ->whereBetween('data_orcamento', [$inicio, $fim])
            ->count();

        $totalVendasDia = (float) Venda::query()
            ->whereBetween('data_venda', [$inicioDia, $fimDia])
            ->sum('total');

        $entradasCaixaDia = (float) MovimentacaoCaixa::query()
            ->where('tipo', MovimentacaoCaixa::TIPO_ENTRADA)
            ->whereBetween('data_movimentacao', [$inicioDia, $fimDia])
            ->sum('valor');

        $saidasCaixaDia = (float) MovimentacaoCaixa::query()
            ->where('tipo', MovimentacaoCaixa::TIPO_SAIDA)
            ->whereBetween('data_movimentacao', [$inicioDia, $fimDia])
            ->sum('valor');

        $saldoCaixaDia = round($entradasCaixaDia - $saidasCaixaDia, 2);

        $qtdVendasDia = (int) Venda::query()
            ->whereBetween('data_venda', [$inicioDia, $fimDia])
            ->count();

        $qtdOrcamentosDia = (int) Orcamento::query()
            ->whereBetween('data_orcamento', [$inicioDia, $fimDia])
            ->count();

        $qtdClientes = (int) Cliente::query()->count();

        $orcamentosPorStatus = [
            'abertos' => (int) Orcamento::query()->where('status', Orcamento::STATUS_ABERTO)->count(),
            'aprovados' => (int) Orcamento::query()->where('status', Orcamento::STATUS_APROVADO)->count(),
            'recusados' => (int) Orcamento::query()->where('status', Orcamento::STATUS_RECUSADO)->count(),
            'convertidos' => (int) Orcamento::query()->where('status', Orcamento::STATUS_CONVERTIDO)->count(),
        ];

        $itensMaisVendidos = $this->itensMaisVendidosNoPeriodo($inicio, $fim);

        $ultimasVendas = Venda::query()
            ->with(['cliente'])
            ->orderByDesc('data_venda')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $ultimasDespesas = Despesa::query()
            ->with(['categoriaDespesa'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $ultimasMovimentacoes = MovimentacaoCaixa::query()
            ->orderByDesc('data_movimentacao')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $recebimentosPorForma = $this->recebimentosPorFormaPagamento($inicio, $fim);

        $despesasPorCategoria = $this->despesasPorCategoriaNoPeriodo($inicio, $fim);

        $labelPeriodo = $this->labelPeriodoPortugues($mes, $ano);
        $labelDataDia = $this->labelDataPortugues($dataDia);

        return view('pages.dashboard.index', [
            'title' => 'Dashboard',
            'inicio' => $inicio,
            'fim' => $fim,
            'mes' => $mes,
            'ano' => $ano,
            'dataDia' => $dataDia,
            'labelDataDia' => $labelDataDia,
            'labelPeriodo' => $labelPeriodo,
            'totalVendasMes' => $totalVendasMes,
            'totalDespesasMes' => $totalDespesasMes,
            'entradasCaixaMes' => $entradasCaixaMes,
            'saidasCaixaMes' => $saidasCaixaMes,
            'saldoCaixaMes' => $saldoCaixaMes,
            'qtdVendasMes' => $qtdVendasMes,
            'qtdOrcamentosMes' => $qtdOrcamentosMes,
            'totalVendasDia' => $totalVendasDia,
            'entradasCaixaDia' => $entradasCaixaDia,
            'saidasCaixaDia' => $saidasCaixaDia,
            'saldoCaixaDia' => $saldoCaixaDia,
            'qtdVendasDia' => $qtdVendasDia,
            'qtdOrcamentosDia' => $qtdOrcamentosDia,
            'qtdClientes' => $qtdClientes,
            'orcamentosPorStatus' => $orcamentosPorStatus,
            'itensMaisVendidos' => $itensMaisVendidos,
            'ultimasVendas' => $ultimasVendas,
            'ultimasDespesas' => $ultimasDespesas,
            'ultimasMovimentacoes' => $ultimasMovimentacoes,
            'recebimentosPorForma' => $recebimentosPorForma,
            'despesasPorCategoria' => $despesasPorCategoria,
        ]);
    }

    private function labelPeriodoPortugues(int $mes, int $ano): string
    {
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];

        $nome = $meses[$mes] ?? 'mês';

        return "{$nome} de {$ano}";
    }

    private function labelDataPortugues(Carbon $data): string
    {
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];
        $d = (int) $data->day;
        $m = (int) $data->month;
        $a = (int) $data->year;
        $nome = $meses[$m] ?? 'mês';

        return "{$d} de {$nome} de {$a}";
    }

    /**
     * Dia usado no bloco “indicadores diários”, sempre dentro do mês/ano selecionados.
     */
    private function resolverDiaNoPeriodo(Request $request, Carbon $inicioMes, Carbon $fimMes): Carbon
    {
        $hoje = now()->startOfDay();
        $inicioD = $inicioMes->copy()->startOfDay();
        $fimD = $fimMes->copy()->startOfDay();

        if (! $request->filled('data_dia')) {
            if ($hoje->between($inicioD, $fimD)) {
                return $hoje->copy();
            }

            return $fimD->copy();
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', (string) $request->input('data_dia'))->startOfDay();
        } catch (\Throwable) {
            return $hoje->between($inicioD, $fimD) ? $hoje->copy() : $fimD->copy();
        }

        if ($parsed->lt($inicioD)) {
            return $inicioD->copy();
        }
        if ($parsed->gt($fimD)) {
            return $fimD->copy();
        }

        return $parsed;
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: int, 3: int}
     */
    private function resolverPeriodo(Request $request): array
    {
        $mesAtual = (int) now()->month;
        $anoAtual = (int) now()->year;

        $mes = (int) $request->input('mes', $mesAtual);
        $ano = (int) $request->input('ano', $anoAtual);

        if ($mes < 1 || $mes > 12) {
            $mes = $mesAtual;
        }
        if ($ano < 2000 || $ano > 2100) {
            $ano = $anoAtual;
        }

        $inicio = Carbon::create($ano, $mes, 1)->startOfDay();
        $fim = $inicio->copy()->endOfMonth()->endOfDay();

        return [$inicio, $fim, $mes, $ano];
    }

    private function queryDespesasNoPeriodo(Carbon $inicio, Carbon $fim)
    {
        $driver = DB::getDriverName();
        $inicioStr = $inicio->toDateString();
        $fimStr = $fim->toDateString();

        $q = Despesa::query();

        if ($driver === 'sqlite') {
            return $q->whereRaw(
                'date(COALESCE(data_pagamento, data_vencimento)) between ? and ?',
                [$inicioStr, $fimStr]
            );
        }

        return $q->whereRaw(
            'DATE(COALESCE(data_pagamento, data_vencimento)) BETWEEN ? AND ?',
            [$inicioStr, $fimStr]
        );
    }

    /**
     * @return Collection<int, object{descricao: string, quantidade_total: float, valor_total: float}>
     */
    private function itensMaisVendidosNoPeriodo(Carbon $inicio, Carbon $fim): Collection
    {
        $linhas = DB::table('venda_itens as vi')
            ->join('vendas as v', 'v.id', '=', 'vi.venda_id')
            ->whereBetween('v.data_venda', [$inicio, $fim])
            ->select([
                'vi.item_id',
                'vi.descricao_item',
                DB::raw('SUM(vi.quantidade) as quantidade_total'),
                DB::raw('SUM(vi.subtotal) as valor_total'),
            ])
            ->groupBy('vi.item_id', 'vi.descricao_item')
            ->orderByDesc('valor_total')
            ->limit(10)
            ->get();

        $ids = $linhas->pluck('item_id')->filter()->unique()->values()->all();
        $nomesItens = $ids !== []
            ? Item::query()->whereIn('id', $ids)->pluck('nome', 'id')
            : collect();

        return $linhas->map(function ($row) use ($nomesItens) {
            $nome = $row->item_id
                ? ($nomesItens[(int) $row->item_id] ?? $row->descricao_item)
                : $row->descricao_item;

            return (object) [
                'descricao' => (string) ($nome ?? '—'),
                'quantidade_total' => (float) $row->quantidade_total,
                'valor_total' => (float) $row->valor_total,
            ];
        });
    }

    /**
     * @return Collection<int, object{nome: string, total: float}>
     */
    private function recebimentosPorFormaPagamento(Carbon $inicio, Carbon $fim): Collection
    {
        $rows = DB::table('recebimentos_venda as r')
            ->join('formas_pagamento as fp', 'fp.id', '=', 'r.forma_pagamento_id')
            ->whereBetween('r.data_recebimento', [$inicio, $fim])
            ->select([
                'fp.nome as forma_nome',
                DB::raw('SUM(r.valor) as total'),
            ])
            ->groupBy('fp.id', 'fp.nome')
            ->orderByDesc('total')
            ->get();

        return $rows->map(fn ($r) => (object) [
            'nome' => (string) $r->forma_nome,
            'total' => (float) $r->total,
        ]);
    }

    /**
     * @return Collection<int, object{nome: string, total: float}>
     */
    private function despesasPorCategoriaNoPeriodo(Carbon $inicio, Carbon $fim): Collection
    {
        $base = $this->queryDespesasNoPeriodo($inicio, $fim);

        $rows = (clone $base)
            ->leftJoin('categorias_despesa as cd', 'cd.id', '=', 'despesas.categoria_despesa_id')
            ->select([
                'despesas.categoria_despesa_id',
                'cd.nome as categoria_nome',
                DB::raw('SUM(despesas.valor) as total'),
            ])
            ->groupBy('despesas.categoria_despesa_id', 'cd.nome')
            ->orderByDesc('total')
            ->get();

        return $rows->map(fn ($r) => (object) [
            'nome' => $r->categoria_nome ? (string) $r->categoria_nome : 'Sem categoria',
            'total' => (float) $r->total,
        ]);
    }
}
