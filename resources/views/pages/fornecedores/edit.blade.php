@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Fornecedor" icon="ecommerce" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div class="space-y-6">
        <x-common.component-card title="Editar Fornecedor">
            <form method="POST" action="{{ route('fornecedores.update', $fornecedor) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nome<span class="text-error-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nome"
                            value="{{ old('nome', $fornecedor->nome) }}"
                            placeholder="Ex: Papelaria Central Ltda."
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('nome') border-red-500 focus:border-red-500 dark:border-red-500 @enderror"
                        />
                        @error('nome')
                            <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Contato
                        </label>
                        <input
                            type="text"
                            name="contato"
                            value="{{ old('contato', $fornecedor->contato) }}"
                            placeholder="Ex: Maria — setor compras"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('contato') border-red-500 focus:border-red-500 dark:border-red-500 @enderror"
                        />
                        @error('contato')
                            <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Telefone
                        </label>
                        <input
                            type="text"
                            name="telefone"
                            value="{{ old('telefone', $fornecedor->telefone) }}"
                            placeholder="Ex: (11) 99999-9999"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('telefone') border-red-500 focus:border-red-500 dark:border-red-500 @enderror"
                        />
                        @error('telefone')
                            <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            E-mail
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $fornecedor->email) }}"
                            placeholder="Ex: contato@fornecedor.com.br"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('email') border-red-500 focus:border-red-500 dark:border-red-500 @enderror"
                        />
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Observações
                    </label>
                    <textarea
                        name="observacoes"
                        rows="4"
                        placeholder="Observações sobre o fornecedor (opcional)"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('observacoes') border-red-500 focus:border-red-500 dark:border-red-500 @enderror"
                    >{{ old('observacoes', $fornecedor->observacoes) }}</textarea>
                    @error('observacoes')
                        <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <a
                        href="{{ route('fornecedores.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                    >
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z"
                                fill="" />
                        </svg>
                        Cancelar
                    </a>

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M10.0002 2.91699C10.4144 2.91699 10.7502 3.25278 10.7502 3.66699V10.2331L12.9698 8.01344C13.2627 7.72055 13.7376 7.72055 14.0305 8.01344C14.3234 8.30634 14.3234 8.78121 14.0305 9.0741L10.5305 12.5741C10.2376 12.867 9.76273 12.867 9.46983 12.5741L5.96983 9.0741C5.67694 8.78121 5.67694 8.30634 5.96983 8.01344C6.26273 7.72055 6.7376 7.72055 7.03049 8.01344L9.25016 10.2331V3.66699C9.25016 3.25278 9.58595 2.91699 10.0002 2.91699ZM4.16683 14.5837C4.58104 14.5837 4.91683 14.9194 4.91683 15.3337C4.91683 15.5178 5.06699 15.667 5.25016 15.667H14.7502C14.9333 15.667 15.0835 15.5178 15.0835 15.3337C15.0835 14.9194 15.4193 14.5837 15.8335 14.5837C16.2477 14.5837 16.5835 14.9194 16.5835 15.3337C16.5835 16.3462 15.7616 17.167 14.7502 17.167H5.25016C4.23864 17.167 3.41683 16.3462 3.41683 15.3337C3.41683 14.9194 3.75262 14.5837 4.16683 14.5837Z"
                                fill="" />
                        </svg>
                        Salvar
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
