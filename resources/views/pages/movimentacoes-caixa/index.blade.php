@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Movimentações de caixa" icon="charts" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    @php
        $fmt = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    @endphp

    <div
        class="space-y-6"
        x-data="{ deleteAction: '', deleteMovimentacaoDescricao: '' }"
    >
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total de entradas</p>
                <p class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-500">{{ $fmt($totalEntradas) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Com base nos filtros aplicados</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total de saídas</p>
                <p class="mt-2 text-2xl font-semibold text-error-600 dark:text-error-500">{{ $fmt($totalSaidas) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Com base nos filtros aplicados</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Saldo do período</p>
                <p
                    @class([
                        'mt-2 text-2xl font-semibold',
                        'text-success-600 dark:text-success-500' => (float) $saldo >= 0,
                        'text-error-600 dark:text-error-500' => (float) $saldo < 0,
                    ])
                >
                    {{ $fmt($saldo) }}
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Entradas − saídas (filtros)</p>
            </div>
        </div>

        <x-common.component-card title="Gerenciar movimentações de caixa" desc="">
            <x-slot:headerActions>
                <a
                    href="{{ route('movimentacoes-caixa.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 3.33301C10.4144 3.33301 10.7502 3.66879 10.7502 4.08301V9.24967H15.9168C16.331 9.24967 16.6668 9.58546 16.6668 9.99967C16.6668 10.4139 16.331 10.7497 15.9168 10.7497H10.7502V15.9163C10.7502 16.3306 10.4144 16.6663 10.0002 16.6663C9.58595 16.6663 9.25016 16.3306 9.25016 15.9163V10.7497H4.0835C3.66928 10.7497 3.3335 10.4139 3.3335 9.99967C3.3335 9.58546 3.66928 9.24967 4.0835 9.24967H9.25016V4.08301C9.25016 3.66879 9.58595 3.33301 10.0002 3.33301Z" fill="" />
                    </svg>
                    Nova movimentação
                </a>
            </x-slot:headerActions>

            <div class="flex flex-col gap-4">
                <form method="GET" action="{{ route('movimentacoes-caixa.index') }}" class="flex flex-col gap-3 xl:flex-row xl:flex-wrap xl:items-end">
                    <div class="min-w-0 flex-1 xl:min-w-[200px] xl:max-w-[260px]">
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Busca (descrição)</label>
                        <input
                            type="text"
                            name="search"
                            value="{{ old('search', $search ?? '') }}"
                            placeholder="Descrição"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        />
                    </div>

                    <div class="w-full xl:w-[150px]">
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Tipo</label>
                        <div class="relative z-20 bg-transparent">
                            <select
                                name="tipo"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            >
                                <option value="">Todos</option>
                                @foreach (\App\Models\MovimentacaoCaixa::TIPO_LABELS as $valor => $rotulo)
                                    <option value="{{ $valor }}" @selected(($tipoFiltro ?? '') === $valor)>{{ $rotulo }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="w-full xl:w-[160px]">
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Origem</label>
                        <div class="relative z-20 bg-transparent">
                            <select
                                name="origem"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            >
                                <option value="">Todas</option>
                                @foreach (\App\Models\MovimentacaoCaixa::ORIGEM_LABELS as $valor => $rotulo)
                                    <option value="{{ $valor }}" @selected(($origemFiltro ?? '') === $valor)>{{ $rotulo }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="w-full xl:w-[200px]">
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Forma de pagamento</label>
                        <div class="relative z-20 bg-transparent">
                            <select
                                name="forma_pagamento_id"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            >
                                <option value="">Todas</option>
                                @foreach ($formasFiltro as $fp)
                                    <option value="{{ $fp->id }}" @selected((string) ($formaPagamentoFiltro ?? '') === (string) $fp->id)>{{ $fp->nome }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="w-full xl:w-[170px]">
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Data inicial</label>
                        <input
                            type="date"
                            name="data_inicio"
                            value="{{ old('data_inicio', $dataInicio ?? '') }}"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        />
                    </div>

                    <div class="w-full xl:w-[170px]">
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Data final</label>
                        <input
                            type="date"
                            name="data_fim"
                            value="{{ old('data_fim', $dataFim ?? '') }}"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        />
                    </div>

                    <div class="flex w-full gap-3 xl:w-auto xl:pb-0.5">
                        <button
                            type="submit"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 xl:flex-initial"
                        >
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.58317 2.91699C5.90027 2.91699 2.9165 5.90076 2.9165 9.58366C2.9165 13.2666 5.90027 16.2503 9.58317 16.2503C11.2159 16.2503 12.7115 15.6634 13.8705 14.6885L16.8408 17.6588C17.1337 17.9517 17.6086 17.9517 17.9015 17.6588C18.1944 17.3659 18.1944 16.8911 17.9015 16.5982L14.9312 13.6278C15.9061 12.4688 16.493 10.9732 16.493 9.34046C16.493 5.65756 13.5092 2.67379 9.82631 2.67379H9.58317V2.91699ZM4.4165 9.58366C4.4165 6.72918 6.72869 4.41699 9.58317 4.41699C12.4376 4.41699 14.7498 6.72918 14.7498 9.58366C14.7498 12.4381 12.4376 14.7503 9.58317 14.7503C6.72869 14.7503 4.4165 12.4381 4.4165 9.58366Z" fill="" />
                            </svg>
                            Filtrar
                        </button>
                        <a
                            href="{{ route('movimentacoes-caixa.index') }}"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] xl:flex-initial"
                        >
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z" fill="" />
                            </svg>
                            Limpar
                        </a>
                    </div>
                </form>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[1200px]">
                        <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Data movimentação</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Tipo</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Origem</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Descrição</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Valor</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Forma pgto.</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Venda</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Despesa</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Cadastro</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movimentacoes as $mov)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ $mov->data_movimentacao->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3.5 sm:px-6">
                                        <x-ui.badge color="{{ \App\Models\MovimentacaoCaixa::tipoBadgeColor($mov->tipo) }}">
                                            {{ \App\Models\MovimentacaoCaixa::TIPO_LABELS[$mov->tipo] ?? $mov->tipo }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3.5 sm:px-6">
                                        <x-ui.badge color="{{ \App\Models\MovimentacaoCaixa::origemBadgeColor($mov->origem) }}">
                                            {{ \App\Models\MovimentacaoCaixa::ORIGEM_LABELS[$mov->origem] ?? $mov->origem }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3.5 sm:px-6">
                                        <span class="block text-theme-sm font-medium text-gray-800 dark:text-gray-200">{{ \Illuminate\Support\Str::limit($mov->descricao, 48) }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                        {{ $fmt($mov->valor) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ $mov->formaPagamento?->nome ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        @if ($mov->venda_id && $mov->venda)
                                            <a href="{{ route('vendas.show', $mov->venda) }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">#{{ $mov->venda->numero }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        @if ($mov->despesa_id && $mov->despesa)
                                            <a href="{{ route('despesas.show', $mov->despesa) }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ \Illuminate\Support\Str::limit($mov->despesa->descricao, 32) }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ optional($mov->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3.5 sm:px-6">
                                        <div class="flex items-center gap-2 sm:gap-3">
                                            <a
                                                href="{{ route('movimentacoes-caixa.show', $mov) }}"
                                                aria-label="Ver movimentação"
                                                class="size-5 cursor-pointer text-gray-700 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"
                                            >
                                                <svg class="stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <a
                                                href="{{ route('movimentacoes-caixa.edit', $mov) }}"
                                                aria-label="Editar movimentação"
                                                class="size-5 cursor-pointer text-gray-700 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"
                                            >
                                                <svg class="stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                                                </svg>
                                            </a>
                                            <button
                                                type="button"
                                                aria-label="Excluir movimentação"
                                                @click="
                                                    deleteAction = {{ Js::from(route('movimentacoes-caixa.destroy', $mov)) }};
                                                    deleteMovimentacaoDescricao = {{ Js::from(\Illuminate\Support\Str::limit($mov->descricao, 80)) }};
                                                    $dispatch('open-delete-movimentacao-modal');
                                                "
                                            >
                                                <svg
                                                    class="size-5 cursor-pointer text-gray-700 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                        Nenhuma movimentação encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($movimentacoes->hasPages())
                <div class="border-t border-gray-100 p-4 sm:p-6 dark:border-gray-800">
                    <div class="flex items-center justify-between gap-2 px-0 py-0 sm:justify-normal">
                        <a
                            href="{{ $movimentacoes->previousPageUrl() ?? '#' }}"
                            @class([
                                'flex items-center gap-2 rounded-lg border p-2 sm:p-2.5 shadow-theme-xs',
                                'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' => $movimentacoes->onFirstPage() === false,
                                'pointer-events-none border-gray-200 bg-gray-100 text-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-600' => $movimentacoes->onFirstPage(),
                            ])
                        >
                            <span>
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M2.58203 9.99868C2.58174 10.1909 2.6549 10.3833 2.80152 10.53L7.79818 15.5301C8.09097 15.8231 8.56584 15.8233 8.85883 15.5305C9.15183 15.2377 9.152 14.7629 8.85921 14.4699L5.13911 10.7472L16.6665 10.7472C17.0807 10.7472 17.4165 10.4114 17.4165 9.99715C17.4165 9.58294 17.0807 9.24715 16.6665 9.24715L5.14456 9.24715L8.85919 5.53016C9.15199 5.23717 9.15184 4.7623 8.85885 4.4695C8.56587 4.1767 8.09099 4.17685 7.79819 4.46984L2.84069 9.43049C2.68224 9.568 2.58203 9.77087 2.58203 9.99715C2.58203 9.99766 2.58203 9.99817 2.58203 9.99868Z" fill=""></path>
                                </svg>
                            </span>
                        </a>

                        <span class="block text-sm font-medium text-gray-700 dark:text-gray-400 sm:hidden">
                            Página {{ $movimentacoes->currentPage() }} de {{ $movimentacoes->lastPage() }}
                        </span>

                        <ul class="hidden items-center gap-0.5 sm:mx-3 sm:flex">
                            @foreach ($movimentacoes->getUrlRange(max(1, $movimentacoes->currentPage() - 2), min($movimentacoes->lastPage(), $movimentacoes->currentPage() + 2)) as $page => $url)
                                <li>
                                    <a
                                        href="{{ $url }}"
                                        @class([
                                            'flex h-10 w-10 items-center justify-center rounded-lg text-sm font-medium',
                                            'bg-brand-500 text-white hover:bg-brand-500 hover:text-white' => $page === $movimentacoes->currentPage(),
                                            'text-gray-700 hover:bg-brand-500 hover:text-white dark:text-gray-400 dark:hover:text-white' => $page !== $movimentacoes->currentPage(),
                                        ])
                                    >
                                        {{ $page }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <a
                            href="{{ $movimentacoes->nextPageUrl() ?? '#' }}"
                            @class([
                                'flex items-center gap-2 rounded-lg border p-2 sm:p-2.5 shadow-theme-xs',
                                'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' => $movimentacoes->hasMorePages(),
                                'pointer-events-none border-gray-200 bg-gray-100 text-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-600' => $movimentacoes->hasMorePages() === false,
                            ])
                        >
                            <span>
                                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M17.4165 9.9986C17.4168 10.1909 17.3437 10.3832 17.197 10.53L12.2004 15.5301C11.9076 15.8231 11.4327 15.8233 11.1397 15.5305C10.8467 15.2377 10.8465 14.7629 11.1393 14.4699L14.8594 10.7472L3.33203 10.7472C2.91782 10.7472 2.58203 10.4114 2.58203 9.99715C2.58203 9.58294 2.91782 9.24715 3.33203 9.24715L14.854 9.24715L11.1393 5.53016C10.8465 5.23717 10.8467 4.7623 11.1397 4.4695C11.4327 4.1767 11.9075 4.17685 12.2003 4.46984L17.1578 9.43049C17.3163 9.568 17.4165 9.77087 17.4165 9.99715C17.4165 9.99763 17.4165 9.99812 17.4165 9.9986Z" fill=""></path>
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>
            @endif
        </x-common.component-card>

        <x-ui.modal @open-delete-movimentacao-modal.window="open = true" :isOpen="false" class="max-w-[520px]">
            <div class="w-full p-6 sm:p-8">
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">Confirmar exclusão</h4>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Tem certeza que deseja excluir a movimentação <span class="font-medium text-gray-700 dark:text-gray-300" x-text="deleteMovimentacaoDescricao"></span>?
                </p>
                <form class="mt-6 flex items-center justify-end gap-3" method="POST" :action="deleteAction">
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                    >
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z" fill="" />
                        </svg>
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-red-600"
                    >
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.91683 2.91699C7.50262 2.91699 7.16683 3.25278 7.16683 3.66699V4.41699H4.50016C4.08595 4.41699 3.75016 4.75278 3.75016 5.16699C3.75016 5.5812 4.08595 5.91699 4.50016 5.91699H5.08011L5.7322 14.2853C5.82205 15.4383 6.78387 16.3337 7.94032 16.3337H12.0601C13.2166 16.3337 14.1784 15.4383 14.2682 14.2853L14.9203 5.91699H15.5002C15.9144 5.91699 16.2502 5.5812 16.2502 5.16699C16.2502 4.75278 15.9144 4.41699 15.5002 4.41699H12.8335V3.66699C12.8335 3.25278 12.4977 2.91699 12.0835 2.91699H7.91683ZM8.66683 4.41699V4.41699H11.3335V4.41699H8.66683ZM7.23358 5.91699L7.87044 14.0903C7.90039 14.4746 8.22099 14.8337 8.60753 14.8337H11.3929C11.7794 14.8337 12.1 14.4746 12.1299 14.0903L12.7667 5.91699H7.23358Z" fill="" />
                        </svg>
                        Excluir
                    </button>
                </form>
            </div>
        </x-ui.modal>
    </div>
@endsection
