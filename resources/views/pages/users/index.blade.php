@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Usuários" />

    @if (session('success'))
        <x-ui.alert variant="success" title="Sucesso" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div class="space-y-6">
        <x-common.component-card title="Gerenciar Usuários" desc="">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form method="GET" action="{{ route('usuarios.index') }}" class="flex w-full gap-3 sm:w-auto">
                    <input
                        type="text"
                        name="search"
                        value="{{ old('search', $search ?? '') }}"
                        placeholder="Buscar por nome ou e-mail"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full sm:w-80 rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                    <button
                        type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex h-11 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium text-white transition"
                    >
                        Buscar
                    </button>
                </form>

                <a
                    href="{{ route('usuarios.create') }}"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex h-11 items-center justify-center gap-2 rounded-lg px-5 text-sm font-medium text-white transition"
                >
                    Novo Usuário
                </a>
            </div>

            <div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full">
                        <thead class="px-6 py-3.5 border-t border-gray-100 border-y bg-gray-50 dark:border-white/[0.05] dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                                    ID
                                </th>
                                <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                                    Nome
                                </th>
                                <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                                    E-mail
                                </th>
                                <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                                    Criado em
                                </th>
                                <th class="px-6 py-3 font-medium text-gray-500 sm:px-6 text-theme-xs dark:text-gray-400 text-start">
                                    Ações
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($users as $usuario)
                                <tr class="border-b border-gray-100 dark:border-white/[0.05]">
                                    <td class="px-4 sm:px-6 py-3.5">
                                        <span class="block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ $usuario->id }}
                                        </span>
                                    </td>

                                    <td class="px-4 sm:px-6 py-3.5">
                                        <span class="block text-theme-sm font-medium text-gray-700 dark:text-gray-400">
                                            {{ $usuario->name }}
                                        </span>
                                    </td>

                                    <td class="px-4 sm:px-6 py-3.5">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ $usuario->email }}
                                        </p>
                                    </td>

                                    <td class="px-4 sm:px-6 py-3.5">
                                        <p class="text-gray-700 text-theme-sm dark:text-gray-400">
                                            {{ optional($usuario->created_at)->format('d/m/Y H:i') }}
                                        </p>
                                    </td>

                                    <td class="px-4 sm:px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <a
                                                href="{{ route('usuarios.edit', $usuario) }}"
                                                aria-label="Editar usuário"
                                                class="text-gray-700 cursor-pointer size-5 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400"
                                            >
                                                <svg class="stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 20h9" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z" />
                                                </svg>
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('usuarios.destroy', $usuario) }}"
                                                onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" aria-label="Excluir usuário">
                                                    <svg
                                                        class="text-gray-700 cursor-pointer size-5 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 sm:px-6 py-8 text-sm text-gray-500 dark:text-gray-400">
                                        Nenhum usuário encontrado.
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

