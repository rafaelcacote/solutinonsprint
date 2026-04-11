@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Dashboard" icon="dashboard" />

    @php
        $fmt = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
        $fmtQtd = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Indicadores do período:
                    <span class="font-medium text-gray-800 dark:text-white/90">{{ $labelPeriodo }}</span>
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Orçamentos por status refletem a situação atual de todos os cadastros; demais totais do mês usam o período acima. O bloco diário usa a data escolhida (sempre dentro desse mês).
                </p>
            </div>

            <form
                method="get"
                action="{{ route('dashboard') }}"
                class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end"
            >
                <div class="w-full sm:w-[160px]">
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Mês</label>
                    <div class="relative z-20 bg-transparent">
                        <select
                            name="mes"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        >
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected((int) $mes === $m)>
                                    {{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                        <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="w-full sm:w-[140px]">
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Ano</label>
                    <input
                        type="number"
                        name="ano"
                        min="2000"
                        max="2100"
                        value="{{ (int) $ano }}"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                </div>

                <div class="w-full sm:w-[170px]">
                    <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Dia (resumo diário)</label>
                    <input
                        type="date"
                        name="data_dia"
                        value="{{ $dataDia->format('Y-m-d') }}"
                        min="{{ $inicio->format('Y-m-d') }}"
                        max="{{ $fim->format('Y-m-d') }}"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                    />
                </div>

                <div class="flex w-full gap-3 sm:w-auto sm:pb-0.5">
                    <button
                        type="submit"
                        class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 sm:flex-initial"
                    >
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.58317 2.91699C5.90027 2.91699 2.9165 5.90076 2.9165 9.58366C2.9165 13.2666 5.90027 16.2503 9.58317 16.2503C11.2159 16.2503 12.7115 15.6634 13.8705 14.6885L16.8408 17.6588C17.1337 17.9517 17.6086 17.9517 17.9015 17.6588C18.1944 17.3659 18.1944 16.8911 17.9015 16.5982L14.9312 13.6278C15.9061 12.4688 16.493 10.9732 16.493 9.34046C16.493 5.65756 13.5092 2.67379 9.82631 2.67379H9.58317V2.91699ZM4.4165 9.58366C4.4165 6.72918 6.72869 4.41699 9.58317 4.41699C12.4376 4.41699 14.7498 6.72918 14.7498 9.58366C14.7498 12.4381 12.4376 14.7503 9.58317 14.7503C6.72869 14.7503 4.4165 12.4381 4.4165 9.58366Z" fill="" />
                        </svg>
                        Atualizar
                    </button>
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] sm:flex-initial"
                    >
                        Mês atual
                    </a>
                </div>
            </form>
        </div>

        {{-- Cards principais --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-500/10">
                    <svg class="text-brand-600 dark:text-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.31641 4H3.49696C4.24468 4 4.87822 4.55068 4.98234 5.29112L5.13429 6.37161M5.13429 6.37161L6.23641 14.2089C6.34053 14.9493 6.97407 15.5 7.72179 15.5L17.0833 15.5C17.6803 15.5 18.2205 15.146 18.4587 14.5986L21.126 8.47023C21.5572 7.4795 20.8312 6.37161 19.7507 6.37161H5.13429Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total de vendas (mês)</span>
                    <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $fmt($totalVendasMes) }}</h4>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-500/10">
                    <svg class="text-orange-600 dark:text-orange-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3v18M5 9h14M5 15h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total de despesas (mês)</span>
                    <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $fmt($totalDespesasMes) }}</h4>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-green-50 dark:bg-green-500/10">
                    <svg class="text-green-600 dark:text-green-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Entradas no caixa (mês)</span>
                    <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $fmt($entradasCaixaMes) }}</h4>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-red-50 dark:bg-red-500/10">
                    <svg class="text-red-600 dark:text-red-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Saídas do caixa (mês)</span>
                    <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $fmt($saidasCaixaMes) }}</h4>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800">
                    <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.00002 12.0957C4.00002 7.67742 7.58174 4.0957 12 4.0957C16.4183 4.0957 20 7.67742 20 12.0957C20 16.514 16.4183 20.0957 12 20.0957H5.06068L6.34317 18.8132C6.48382 18.6726 6.56284 18.4818 6.56284 18.2829C6.56284 18.084 6.48382 17.8932 6.34317 17.7526C4.89463 16.304 4.00002 14.305 4.00002 12.0957Z" fill="" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Saldo do caixa (mês)</span>
                    <h4
                        @class([
                            'mt-2 text-xl font-bold',
                            'text-gray-800 dark:text-white/90' => $saldoCaixaMes >= 0,
                            'text-red-600 dark:text-red-400' => $saldoCaixaMes < 0,
                        ])
                    >
                        {{ $fmt($saldoCaixaMes) }}
                    </h4>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-500/10">
                    <svg class="text-sky-600 dark:text-sky-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 7h12M8 12h12M8 17h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Qtd. vendas (mês)</span>
                    <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($qtdVendasMes, 0, ',', '.') }}</h4>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-500/10">
                    <svg class="text-indigo-600 dark:text-indigo-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.75586 5.50098C7.75586 5.08676 8.09165 4.75098 8.50586 4.75098H18.4985C18.9127 4.75098 19.2485 5.08676 19.2485 5.50098L19.2485 15.4956C19.2485 15.9098 18.9127 16.2456 18.4985 16.2456H8.50586C8.09165 16.2456 7.75586 15.9098 7.75586 15.4956V5.50098Z" fill="currentColor" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Qtd. orçamentos (mês)</span>
                    <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($qtdOrcamentosMes, 0, ',', '.') }}</h4>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10">
                    <svg class="text-purple-600 dark:text-purple-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5Z" fill="currentColor" />
                    </svg>
                </div>
                <div class="mt-5">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Clientes cadastrados</span>
                    <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($qtdClientes, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>

        {{-- Indicadores do dia (data do formulário; limitada ao mês/ano selecionados) --}}
        <div>
            <p class="mb-3 text-sm font-medium text-gray-800 dark:text-white/90">
                Indicadores do dia: <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $labelDataDia }}</span>
            </p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-6">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
                        <svg class="text-brand-600 dark:text-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.31641 4H3.49696C4.24468 4 4.87822 4.55068 4.98234 5.29112L5.13429 6.37161M5.13429 6.37161L6.23641 14.2089C6.34053 14.9493 6.97407 15.5 7.72179 15.5L17.0833 15.5C17.6803 15.5 18.2205 15.146 18.4587 14.5986L21.126 8.47023C21.5572 7.4795 20.8312 6.37161 19.7507 6.37161H5.13429Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total de vendas (dia)</span>
                        <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $fmt($totalVendasDia) }}</h4>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 dark:bg-green-500/10">
                        <svg class="text-green-600 dark:text-green-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Entradas no caixa (dia)</span>
                        <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $fmt($entradasCaixaDia) }}</h4>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 dark:bg-red-500/10">
                        <svg class="text-red-600 dark:text-red-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12h14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Saídas do caixa (dia)</span>
                        <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ $fmt($saidasCaixaDia) }}</h4>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                        <svg class="fill-gray-800 dark:fill-white/90" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.00002 12.0957C4.00002 7.67742 7.58174 4.0957 12 4.0957C16.4183 4.0957 20 7.67742 20 12.0957C20 16.514 16.4183 20.0957 12 20.0957H5.06068L6.34317 18.8132C6.48382 18.6726 6.56284 18.4818 6.56284 18.2829C6.56284 18.084 6.48382 17.8932 6.34317 17.7526C4.89463 16.304 4.00002 14.305 4.00002 12.0957Z" fill="" />
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Saldo do caixa (dia)</span>
                        <h4
                            @class([
                                'mt-2 text-xl font-bold',
                                'text-gray-800 dark:text-white/90' => $saldoCaixaDia >= 0,
                                'text-red-600 dark:text-red-400' => $saldoCaixaDia < 0,
                            ])
                        >
                            {{ $fmt($saldoCaixaDia) }}
                        </h4>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-500/10">
                        <svg class="text-sky-600 dark:text-sky-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 7h12M8 12h12M8 17h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Qtd. vendas (dia)</span>
                        <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($qtdVendasDia, 0, ',', '.') }}</h4>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/10">
                        <svg class="text-indigo-600 dark:text-indigo-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.75586 5.50098C7.75586 5.08676 8.09165 4.75098 8.50586 4.75098H18.4985C18.9127 4.75098 19.2485 5.08676 19.2485 5.50098L19.2485 15.4956C19.2485 15.9098 18.9127 16.2456 18.4985 16.2456H8.50586C8.09165 16.2456 7.75586 15.9098 7.75586 15.4956V5.50098Z" fill="currentColor" />
                        </svg>
                    </div>
                    <div class="mt-5">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Qtd. orçamentos (dia)</span>
                        <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white/90">{{ number_format($qtdOrcamentosDia, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            {{-- Orçamentos por status --}}
            <x-common.component-card title="Orçamentos por situação" desc="Contagem atual no sistema (todos os períodos).">
                <x-slot:headerActions>
                    <a
                        href="{{ route('orcamentos.index') }}"
                        class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400"
                    >
                        Ver orçamentos
                    </a>
                </x-slot:headerActions>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-white/[0.05] dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Abertos</p>
                        <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ number_format($orcamentosPorStatus['abertos'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-white/[0.05] dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Aprovados</p>
                        <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ number_format($orcamentosPorStatus['aprovados'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-white/[0.05] dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Recusados</p>
                        <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ number_format($orcamentosPorStatus['recusados'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-white/[0.05] dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Convertidos</p>
                        <p class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ number_format($orcamentosPorStatus['convertidos'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </x-common.component-card>

            {{-- Recebimentos por forma de pagamento --}}
            <x-common.component-card
                title="Recebimentos por forma de pagamento"
                desc="Soma dos recebimentos de vendas no período selecionado."
            >
                <x-slot:headerActions>
                    <a
                        href="{{ route('vendas.index') }}"
                        class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400"
                    >
                        Ver vendas
                    </a>
                </x-slot:headerActions>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="max-w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[320px]">
                            <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Forma</th>
                                    <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recebimentosPorForma as $linha)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3 text-theme-sm text-gray-800 dark:text-gray-200 sm:px-6">
                                            {{ $linha->nome }}
                                        </td>
                                        <td class="px-4 py-3 text-end text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                            {{ $fmt($linha->total) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                            Nenhum recebimento no período.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            {{-- Itens mais vendidos --}}
            <x-common.component-card
                title="Itens mais vendidos"
                desc="Top 10 por valor no período (itens catalogados ou descrição avulsa)."
            >
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="max-w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[520px]">
                            <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Item / descrição</th>
                                    <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Qtd.</th>
                                    <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($itensMaisVendidos as $linha)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3 text-theme-sm text-gray-800 dark:text-gray-200 sm:px-6">
                                            {{ \Illuminate\Support\Str::limit($linha->descricao, 64) }}
                                        </td>
                                        <td class="px-4 py-3 text-end text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                            {{ $fmtQtd($linha->quantidade_total) }}
                                        </td>
                                        <td class="px-4 py-3 text-end text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                            {{ $fmt($linha->valor_total) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                            Nenhuma venda de itens no período.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-common.component-card>

            {{-- Despesas por categoria --}}
            <x-common.component-card
                title="Despesas por categoria"
                desc="Total no período conforme data de pagamento ou, se pendente, data de vencimento."
            >
                <x-slot:headerActions>
                    <a
                        href="{{ route('despesas.index') }}"
                        class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400"
                    >
                        Ver despesas
                    </a>
                </x-slot:headerActions>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="max-w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[320px]">
                            <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Categoria</th>
                                    <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($despesasPorCategoria as $linha)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3 text-theme-sm text-gray-800 dark:text-gray-200 sm:px-6">
                                            {{ $linha->nome }}
                                        </td>
                                        <td class="px-4 py-3 text-end text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                            {{ $fmt($linha->total) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                            Nenhuma despesa no período.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            {{-- Últimas vendas --}}
            <x-common.component-card title="Últimas vendas" desc="Até 10 registros mais recentes por data da venda.">
                <x-slot:headerActions>
                    <a
                        href="{{ route('vendas.index') }}"
                        class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400"
                    >
                        Ver todas
                    </a>
                </x-slot:headerActions>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="max-w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[640px]">
                            <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Número</th>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Cliente</th>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Data</th>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Status</th>
                                    <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ultimasVendas as $venda)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3 sm:px-6">
                                            <a
                                                href="{{ route('vendas.show', $venda) }}"
                                                class="text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                                            >
                                                {{ $venda->numero }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                            {{ $venda->cliente?->nome ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                            {{ $venda->data_venda->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 sm:px-6">
                                            <x-ui.badge color="{{ \App\Models\Venda::statusBadgeColor($venda->status) }}">
                                                {{ \App\Models\Venda::STATUS_LABELS[$venda->status] ?? $venda->status }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-3 text-end text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                            {{ $fmt($venda->total) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                            Nenhuma venda cadastrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-common.component-card>

            {{-- Últimas despesas --}}
            <x-common.component-card title="Últimas despesas" desc="Até 10 lançamentos mais recentes.">
                <x-slot:headerActions>
                    <a
                        href="{{ route('despesas.index') }}"
                        class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400"
                    >
                        Ver todas
                    </a>
                </x-slot:headerActions>

                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="max-w-full overflow-x-auto custom-scrollbar">
                        <table class="w-full min-w-[900px]">
                            <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Descrição</th>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Categoria</th>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Tipo</th>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Status</th>
                                    <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Valor</th>
                                    <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ultimasDespesas as $despesa)
                                    <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                        <td class="px-4 py-3 sm:px-6">
                                            <a
                                                href="{{ route('despesas.show', $despesa) }}"
                                                class="block text-theme-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
                                            >
                                                {{ \Illuminate\Support\Str::limit($despesa->descricao, 40) }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                            {{ $despesa->categoriaDespesa?->nome ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 sm:px-6">
                                            <x-ui.badge color="{{ \App\Models\Despesa::tipoBadgeColor($despesa->tipo) }}">
                                                {{ \App\Models\Despesa::TIPO_LABELS[$despesa->tipo] ?? $despesa->tipo }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-3 sm:px-6">
                                            <x-ui.badge color="{{ \App\Models\Despesa::statusBadgeColor($despesa->status) }}">
                                                {{ \App\Models\Despesa::STATUS_LABELS[$despesa->status] ?? $despesa->status }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-3 text-end text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                            {{ $fmt($despesa->valor) }}
                                        </td>
                                        <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                            {{ ($despesa->data_pagamento ?? $despesa->data_vencimento)?->format('d/m/Y') ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                            Nenhuma despesa cadastrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-common.component-card>
        </div>

        {{-- Últimas movimentações de caixa --}}
        <x-common.component-card title="Últimas movimentações de caixa" desc="Até 10 registros mais recentes.">
            <x-slot:headerActions>
                <a
                    href="{{ route('movimentacoes-caixa.index') }}"
                    class="text-sm font-medium text-brand-500 hover:text-brand-600 dark:text-brand-400"
                >
                    Ver todas
                </a>
            </x-slot:headerActions>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[800px]">
                        <thead class="border-y border-gray-100 bg-gray-50 px-6 py-3.5 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Data</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Tipo</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Origem</th>
                                <th class="px-4 py-3 text-start text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Descrição</th>
                                <th class="px-4 py-3 text-end text-theme-xs font-medium text-gray-500 dark:text-gray-400 sm:px-6">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ultimasMovimentacoes as $mov)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ $mov->data_movimentacao->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 sm:px-6">
                                        <x-ui.badge color="{{ \App\Models\MovimentacaoCaixa::tipoBadgeColor($mov->tipo) }}">
                                            {{ \App\Models\MovimentacaoCaixa::TIPO_LABELS[$mov->tipo] ?? $mov->tipo }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3 sm:px-6">
                                        <x-ui.badge color="{{ \App\Models\MovimentacaoCaixa::origemBadgeColor($mov->origem) }}">
                                            {{ \App\Models\MovimentacaoCaixa::ORIGEM_LABELS[$mov->origem] ?? $mov->origem }}
                                        </x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3 text-theme-sm text-gray-700 dark:text-gray-400 sm:px-6">
                                        {{ \Illuminate\Support\Str::limit($mov->descricao ?? '—', 48) }}
                                    </td>
                                    <td class="px-4 py-3 text-end text-theme-sm font-medium text-gray-800 dark:text-gray-300 sm:px-6">
                                        {{ $fmt($mov->valor) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                        Nenhuma movimentação cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-common.component-card>
    </div>
@endsection
