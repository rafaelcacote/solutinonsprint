@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="'Venda '.$venda->numero" icon="ecommerce" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    @php
        $fmt = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
        $recebido = $venda->totalRecebido();
        $saldo = $venda->saldoRestante();
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a
            href="{{ route('vendas.index') }}"
            class="inline-flex w-fit items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
        >
            <svg class="size-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar à listagem
        </a>
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('vendas.edit', $venda) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
            >
                <svg class="size-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                </svg>
                Editar
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <x-common.component-card title="Cabeçalho">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Número</p>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $venda->numero }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Cliente</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        {{ $venda->cliente?->nome ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Orçamento</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        @if ($venda->orcamento)
                            <a href="{{ route('orcamentos.show', $venda->orcamento) }}" class="text-brand-600 hover:underline dark:text-brand-400">{{ $venda->orcamento->numero }}</a>
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data da venda</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        {{ $venda->data_venda->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                    <div class="mt-1">
                        <x-ui.badge color="{{ \App\Models\Venda::statusBadgeColor($venda->status) }}">
                            {{ \App\Models\Venda::STATUS_LABELS[$venda->status] ?? $venda->status }}
                        </x-ui.badge>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data de cadastro</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        {{ optional($venda->created_at)->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>

            @if ($venda->observacoes)
                <div class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Observações</p>
                    <p class="mt-1 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">{{ $venda->observacoes }}</p>
                </div>
            @endif
        </x-common.component-card>

        <x-common.component-card title="Itens">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[900px]">
                        <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Descrição</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Un.</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Qtd</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Unit.</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Subtotal</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Custo</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Dim./m²</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($venda->itens as $li)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-4 py-3.5 sm:px-6">
                                        <span class="block text-theme-sm font-medium text-gray-800 dark:text-gray-200">{{ $li->descricao_item }}</span>
                                        @if ($li->item)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Ref.: {{ $li->item->nome }}</span>
                                        @endif
                                        @if ($li->categoria)
                                            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Categoria: {{ $li->categoria->nome }}</span>
                                        @endif
                                        @if ($li->observacoes)
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $li->observacoes }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">{{ $li->unidade_medida }}</td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ number_format((float) $li->quantidade, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ $fmt($li->valor_unitario) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                        {{ $fmt($li->subtotal) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-600 dark:text-gray-400 sm:px-6">
                                        @if ($li->custo_total !== null)
                                            {{ $fmt($li->custo_total) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-600 dark:text-gray-400 sm:px-6">
                                        @php
                                            $dims = collect([$li->largura, $li->altura, $li->metragem])
                                                ->filter(fn ($v) => $v !== null && $v !== '')
                                                ->map(fn ($v) => number_format((float) $v, 2, ',', '.'))
                                                ->implode(' / ');
                                        @endphp
                                        {{ $dims !== '' ? $dims : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                        Nenhum item nesta venda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Recebimentos">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full">
                        <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Forma de pagamento</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Valor</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Data</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Obs.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($venda->recebimentos as $rec)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-800 dark:text-gray-200 sm:px-6">
                                        {{ $rec->formaPagamento?->nome ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                        {{ $fmt($rec->valor) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ $rec->data_recebimento->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-600 dark:text-gray-400 sm:px-6">
                                        {{ $rec->observacoes ?: '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                        Nenhum recebimento registrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-common.component-card>

        <x-common.component-card title="Totais">
            <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:ml-auto sm:max-w-md">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Subtotal (itens)</span>
                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $fmt($venda->subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Desconto</span>
                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $fmt($venda->desconto) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3 text-sm dark:border-gray-700">
                    <span class="font-semibold text-gray-800 dark:text-white/90">Total da venda</span>
                    <span class="font-bold text-brand-600 dark:text-brand-400">{{ $fmt($venda->total) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total recebido</span>
                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $fmt($recebido) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3 text-base dark:border-gray-700">
                    <span class="font-semibold text-gray-800 dark:text-white/90">Saldo restante</span>
                    <span class="font-bold text-gray-800 dark:text-white/90">{{ $fmt($saldo) }}</span>
                </div>
            </div>
        </x-common.component-card>
    </div>
@endsection
