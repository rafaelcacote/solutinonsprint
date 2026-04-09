@php
    $tipoDefault = old('tipo', $movimentacao !== null ? $movimentacao->tipo : \App\Models\MovimentacaoCaixa::TIPO_ENTRADA);
    $origemDefault = old('origem', $movimentacao !== null ? $movimentacao->origem : \App\Models\MovimentacaoCaixa::ORIGEM_APORTE);
    $descricaoDefault = old('descricao', $movimentacao !== null ? $movimentacao->descricao : '');
    $valorDefault = old('valor', $movimentacao !== null ? (string) $movimentacao->valor : '');
    $formaDefault = old('forma_pagamento_id', $movimentacao !== null ? $movimentacao->forma_pagamento_id : '');
    $vendaDefault = old('venda_id', $movimentacao !== null ? $movimentacao->venda_id : '');
    $despesaDefault = old('despesa_id', $movimentacao !== null ? $movimentacao->despesa_id : '');
    $dataMovDefault = old(
        'data_movimentacao',
        $movimentacao !== null && $movimentacao->data_movimentacao
            ? $movimentacao->data_movimentacao->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i')
    );
@endphp

<x-common.component-card title="{{ $movimentacao ? 'Editar movimentação' : 'Nova movimentação' }}">
    <form method="POST" action="{{ $formAction }}" class="space-y-5">
        @csrf
        @if (strtoupper($formMethod) !== 'POST')
            @method($formMethod)
        @endif

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Tipo<span class="text-error-500">*</span>
                </label>
                <div class="relative z-20 bg-transparent">
                    <select
                        name="tipo"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('tipo') border-red-500 dark:border-red-500 @enderror"
                    >
                        @foreach (\App\Models\MovimentacaoCaixa::TIPO_LABELS as $valor => $rotulo)
                            <option value="{{ $valor }}" @selected($tipoDefault === $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                @error('tipo')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Origem<span class="text-error-500">*</span>
                </label>
                <div class="relative z-20 bg-transparent">
                    <select
                        name="origem"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('origem') border-red-500 dark:border-red-500 @enderror"
                    >
                        @foreach (\App\Models\MovimentacaoCaixa::ORIGEM_LABELS as $valor => $rotulo)
                            <option value="{{ $valor }}" @selected($origemDefault === $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                @error('origem')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Descrição<span class="text-error-500">*</span>
                </label>
                <input
                    type="text"
                    name="descricao"
                    value="{{ $descricaoDefault }}"
                    placeholder="Ex.: Venda balcão, retirada sócio, ajuste de caixa"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('descricao') border-red-500 dark:border-red-500 @enderror"
                />
                @error('descricao')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Valor (R$)<span class="text-error-500">*</span>
                </label>
                <input
                    type="text"
                    name="valor"
                    value="{{ $valorDefault }}"
                    inputmode="decimal"
                    placeholder="0,00"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('valor') border-red-500 dark:border-red-500 @enderror"
                />
                @error('valor')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Informe um valor maior que zero.</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Data e hora da movimentação<span class="text-error-500">*</span>
                </label>
                <input
                    type="datetime-local"
                    name="data_movimentacao"
                    value="{{ $dataMovDefault }}"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('data_movimentacao') border-red-500 dark:border-red-500 @enderror"
                />
                @error('data_movimentacao')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Forma de pagamento</label>
                <div class="relative z-20 bg-transparent">
                    <select
                        name="forma_pagamento_id"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('forma_pagamento_id') border-red-500 dark:border-red-500 @enderror"
                    >
                        <option value="">Nenhuma</option>
                        @foreach ($formasPagamento as $fp)
                            <option value="{{ $fp->id }}" @selected((string) $formaDefault === (string) $fp->id)>{{ $fp->nome }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                @error('forma_pagamento_id')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Venda vinculada</label>
                <div class="relative z-20 bg-transparent">
                    <select
                        name="venda_id"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('venda_id') border-red-500 dark:border-red-500 @enderror"
                    >
                        <option value="">Nenhuma</option>
                        @foreach ($vendas as $v)
                            <option value="{{ $v->id }}" @selected((string) $vendaDefault === (string) $v->id)>
                                #{{ $v->numero }} — R$ {{ number_format((float) $v->total, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                @error('venda_id')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Útil quando a origem for <strong>Venda</strong> (opcional).</p>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Despesa vinculada</label>
                <div class="relative z-20 bg-transparent">
                    <select
                        name="despesa_id"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('despesa_id') border-red-500 dark:border-red-500 @enderror"
                    >
                        <option value="">Nenhuma</option>
                        @foreach ($despesas as $d)
                            <option value="{{ $d->id }}" @selected((string) $despesaDefault === (string) $d->id)>
                                {{ \Illuminate\Support\Str::limit($d->descricao, 80) }}
                            </option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.79199 7.396L10.0003 12.6043L15.2087 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </div>
                @error('despesa_id')
                    <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Útil quando a origem for <strong>Despesa</strong> (opcional).</p>
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a
                href="{{ route('movimentacoes-caixa.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
            >
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z" fill="" />
                </svg>
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
            >
                <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 2.91699C10.4144 2.91699 10.7502 3.25278 10.7502 3.66699V10.2331L12.9698 8.01344C13.2627 7.72055 13.7376 7.72055 14.0305 8.01344C14.3234 8.30634 14.3234 8.78121 14.0305 9.0741L10.5305 12.5741C10.2376 12.867 9.76273 12.867 9.46983 12.5741L5.96983 9.0741C5.67694 8.78121 5.67694 8.30634 5.96983 8.01344C6.26273 7.72055 6.7376 7.72055 7.03049 8.01344L9.25016 10.2331V3.66699C9.25016 3.25278 9.58595 2.91699 10.0002 2.91699ZM4.16683 14.5837C4.58104 14.5837 4.91683 14.9194 4.91683 15.3337C4.91683 15.5178 5.06699 15.667 5.25016 15.667H14.7502C14.9333 15.667 15.0835 15.5178 15.0835 15.3337C15.0835 14.9194 15.4193 14.5837 15.8335 14.5837C16.2477 14.5837 16.5835 14.9194 16.5835 15.3337C16.5835 16.3462 15.7616 17.167 14.7502 17.167H5.25016C4.23864 17.167 3.41683 16.3462 3.41683 15.3337C3.41683 14.9194 3.75262 14.5837 4.16683 14.5837Z" fill="" />
                </svg>
                Salvar
            </button>
        </div>
    </form>
</x-common.component-card>
