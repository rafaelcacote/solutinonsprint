@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Detalhe da movimentação" icon="charts" />

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
            href="{{ route('movimentacoes-caixa.index') }}"
            class="inline-flex w-fit items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
        >
            <svg class="size-5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Voltar à listagem
        </a>
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('movimentacoes-caixa.edit', $movimentacao) }}"
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
        <x-common.component-card title="Movimentação de caixa">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div class="md:col-span-2 lg:col-span-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Descrição</p>
                    <p class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">{{ $movimentacao->descricao }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tipo</p>
                    <div class="mt-1">
                        <x-ui.badge color="{{ \App\Models\MovimentacaoCaixa::tipoBadgeColor($movimentacao->tipo) }}">
                            {{ \App\Models\MovimentacaoCaixa::TIPO_LABELS[$movimentacao->tipo] ?? $movimentacao->tipo }}
                        </x-ui.badge>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Origem</p>
                    <div class="mt-1">
                        <x-ui.badge color="{{ \App\Models\MovimentacaoCaixa::origemBadgeColor($movimentacao->origem) }}">
                            {{ \App\Models\MovimentacaoCaixa::ORIGEM_LABELS[$movimentacao->origem] ?? $movimentacao->origem }}
                        </x-ui.badge>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Valor</p>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $fmt($movimentacao->valor) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Forma de pagamento</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $movimentacao->formaPagamento?->nome ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data da movimentação</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ $movimentacao->data_movimentacao->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Venda vinculada</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        @if ($movimentacao->venda_id && $movimentacao->venda)
                            <a href="{{ route('vendas.show', $movimentacao->venda) }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">#{{ $movimentacao->venda->numero }}</a>
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Despesa vinculada</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">
                        @if ($movimentacao->despesa_id && $movimentacao->despesa)
                            <a href="{{ route('despesas.show', $movimentacao->despesa) }}" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">{{ $movimentacao->despesa->descricao }}</a>
                        @else
                            —
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Cadastro</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ optional($movimentacao->created_at)->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Última atualização</p>
                    <p class="mt-1 text-sm text-gray-800 dark:text-white/90">{{ optional($movimentacao->updated_at)->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </x-common.component-card>
    </div>
@endsection
