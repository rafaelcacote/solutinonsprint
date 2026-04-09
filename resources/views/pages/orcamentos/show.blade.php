@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="'Orçamento '.$orcamento->numero" icon="task" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    @php
        $fmt = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    @endphp

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a
            href="{{ route('orcamentos.index') }}"
            class="inline-flex w-fit items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
        >
            <svg class="stroke-current size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar à listagem
        </a>
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('orcamentos.edit', $orcamento) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
            >
                <svg class="stroke-current size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $orcamento->numero }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Cliente</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        {{ $orcamento->cliente?->nome ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data do orçamento</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        {{ $orcamento->data_orcamento->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                    <div class="mt-1">
                        <x-ui.badge color="{{ \App\Models\Orcamento::statusBadgeColor($orcamento->status) }}">
                            {{ \App\Models\Orcamento::STATUS_LABELS[$orcamento->status] ?? $orcamento->status }}
                        </x-ui.badge>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Validade (dias)</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $orcamento->validade_dias }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data de cadastro</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        {{ optional($orcamento->created_at)->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>

            @if ($orcamento->observacoes)
                <div class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Observações</p>
                    <p class="mt-1 whitespace-pre-wrap text-sm text-gray-700 dark:text-gray-300">{{ $orcamento->observacoes }}</p>
                </div>
            @endif
        </x-common.component-card>

        <x-common.component-card title="Itens">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full">
                        <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Descrição</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Qtd</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Unit.</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Subtotal</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Dim./m²</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orcamento->itens as $li)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-4 py-3.5 sm:px-6">
                                        <span class="block text-theme-sm font-medium text-gray-800 dark:text-gray-200">{{ $li->descricao_item }}</span>
                                        @if ($li->item)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Ref.: {{ $li->item->nome }}</span>
                                        @endif
                                        @if ($li->observacoes)
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $li->observacoes }}</p>
                                        @endif
                                    </td>
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
                                    <td colspan="5" class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                        Nenhum item neste orçamento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:ml-auto sm:max-w-md">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $fmt($orcamento->subtotal) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Desconto</span>
                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $fmt($orcamento->desconto) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-3 text-base dark:border-gray-700">
                    <span class="font-semibold text-gray-800 dark:text-white/90">Total</span>
                    <span class="font-bold text-brand-600 dark:text-brand-400">{{ $fmt($orcamento->total) }}</span>
                </div>
            </div>
        </x-common.component-card>
    </div>
@endsection
