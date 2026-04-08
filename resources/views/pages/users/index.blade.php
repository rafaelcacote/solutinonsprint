@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Usuários" icon="user-profile" />

    @if (session('success'))
        <x-ui.success-notification title="Success Notification" message="{{ session('success') }}" />
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Atenção" message="{{ session('error') }}" />
    @endif

    <div
        class="space-y-6"
        x-data="{ deleteAction: '', deleteUserName: '', passwordAction: '', passwordUserName: '', passwordUserId: '' }"
        x-init="
            @if ($errors->has('new_password') || $errors->has('new_password_confirmation'))
                passwordUserId = {{ Js::from(old('_password_user_id')) }};
                passwordAction = {{ Js::from(route('usuarios.update-password', old('_password_user_id', 0))) }};
                passwordUserName = {{ Js::from(old('_password_user_name')) }};
                $nextTick(() => $dispatch('open-password-user-modal'));
            @endif
        "
    >
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
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                    >
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M9.58317 2.91699C5.90027 2.91699 2.9165 5.90076 2.9165 9.58366C2.9165 13.2666 5.90027 16.2503 9.58317 16.2503C11.2159 16.2503 12.7115 15.6634 13.8705 14.6885L16.8408 17.6588C17.1337 17.9517 17.6086 17.9517 17.9015 17.6588C18.1944 17.3659 18.1944 16.8911 17.9015 16.5982L14.9312 13.6278C15.9061 12.4688 16.493 10.9732 16.493 9.34046C16.493 5.65756 13.5092 2.67379 9.82631 2.67379H9.58317V2.91699ZM4.4165 9.58366C4.4165 6.72918 6.72869 4.41699 9.58317 4.41699C12.4376 4.41699 14.7498 6.72918 14.7498 9.58366C14.7498 12.4381 12.4376 14.7503 9.58317 14.7503C6.72869 14.7503 4.4165 12.4381 4.4165 9.58366Z"
                                fill="" />
                        </svg>
                        Buscar
                    </button>
                    <a
                        href="{{ route('usuarios.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03]"
                    >
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z"
                                fill="" />
                        </svg>
                        Limpar
                    </a>
                </form>

                <a
                    href="{{ route('usuarios.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                >
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M10.0002 3.33301C10.4144 3.33301 10.7502 3.66879 10.7502 4.08301V9.24967H15.9168C16.331 9.24967 16.6668 9.58546 16.6668 9.99967C16.6668 10.4139 16.331 10.7497 15.9168 10.7497H10.7502V15.9163C10.7502 16.3306 10.4144 16.6663 10.0002 16.6663C9.58595 16.6663 9.25016 16.3306 9.25016 15.9163V10.7497H4.0835C3.66928 10.7497 3.3335 10.4139 3.3335 9.99967C3.3335 9.58546 3.66928 9.24967 4.0835 9.24967H9.25016V4.08301C9.25016 3.66879 9.58595 3.33301 10.0002 3.33301Z"
                            fill="" />
                    </svg>
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

                                            <button
                                                type="button"
                                                aria-label="Editar senha do usuário"
                                                @click="
                                                    passwordUserId = {{ Js::from($usuario->id) }};
                                                    passwordAction = {{ Js::from(route('usuarios.update-password', $usuario)) }};
                                                    passwordUserName = {{ Js::from($usuario->name) }};
                                                    $dispatch('open-password-user-modal');
                                                "
                                            >
                                                <svg
                                                    class="text-gray-700 cursor-pointer size-5 hover:text-amber-500 dark:text-gray-400 dark:hover:text-amber-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2zm3-10V9a3 3 0 016 0v2H9z" />
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                aria-label="Excluir usuário"
                                                @click="
                                                    deleteAction = {{ Js::from(route('usuarios.destroy', $usuario)) }};
                                                    deleteUserName = {{ Js::from($usuario->name) }};
                                                    $dispatch('open-delete-user-modal');
                                                "
                                            >
                                                <svg
                                                    class="text-gray-700 cursor-pointer size-5 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
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

        <x-ui.modal @open-password-user-modal.window="open = true" :isOpen="false" class="max-w-[520px]">
            <div class="w-full p-6 sm:p-8">
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                    Editar senha
                </h4>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Defina uma nova senha para o usuário <span class="font-medium text-gray-700 dark:text-gray-300"
                        x-text="passwordUserName"></span>.
                </p>

                <form class="mt-6 space-y-4" method="POST" :action="passwordAction">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_password_user_id" :value="passwordUserId">
                    <input type="hidden" name="_password_user_name" :value="passwordUserName">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Nova senha<span class="text-error-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="new_password"
                            placeholder="Digite a nova senha"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('new_password') border-red-500 focus:border-red-500 dark:border-red-500 @enderror"
                        />
                        @error('new_password')
                            <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Confirmar nova senha<span class="text-error-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="new_password_confirmation"
                            placeholder="Repita a nova senha"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('new_password_confirmation') border-red-500 focus:border-red-500 dark:border-red-500 @enderror"
                        />
                        @error('new_password_confirmation')
                            <p class="mt-1.5 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button
                            type="button"
                            @click="open = false"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                        >
                            Salvar senha
                        </button>
                    </div>
                </form>
            </div>
        </x-ui.modal>

        <x-ui.modal @open-delete-user-modal.window="open = true" :isOpen="false" class="max-w-[520px]">
        <div class="w-full p-6 sm:p-8">
            <h4 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                Confirmar exclusão
            </h4>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Tem certeza que deseja excluir o usuário <span class="font-medium text-gray-700 dark:text-gray-300"
                    x-text="deleteUserName"></span>?
            </p>

            <form class="mt-6 flex items-center justify-end gap-3" method="POST" :action="deleteAction">
                @csrf
                @method('DELETE')
                <button
                    type="button"
                    @click="open = false"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]"
                >
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.21967 5.21967C5.51256 4.92678 5.98744 4.92678 6.28033 5.21967L10 8.93934L13.7197 5.21967C14.0126 4.92678 14.4874 4.92678 14.7803 5.21967C15.0732 5.51256 15.0732 5.98744 14.7803 6.28033L11.0607 10L14.7803 13.7197C15.0732 14.0126 15.0732 14.4874 14.7803 14.7803C14.4874 15.0732 14.0126 15.0732 13.7197 14.7803L10 11.0607L6.28033 14.7803C5.98744 15.0732 5.51256 15.0732 5.21967 14.7803C4.92678 14.4874 4.92678 14.0126 5.21967 13.7197L8.93934 10L5.21967 6.28033C4.92678 5.98744 4.92678 5.51256 5.21967 5.21967Z"
                            fill="" />
                    </svg>
                    Cancelar
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-red-600"
                >
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M7.91683 2.91699C7.50262 2.91699 7.16683 3.25278 7.16683 3.66699V4.41699H4.50016C4.08595 4.41699 3.75016 4.75278 3.75016 5.16699C3.75016 5.5812 4.08595 5.91699 4.50016 5.91699H5.08011L5.7322 14.2853C5.82205 15.4383 6.78387 16.3337 7.94032 16.3337H12.0601C13.2166 16.3337 14.1784 15.4383 14.2682 14.2853L14.9203 5.91699H15.5002C15.9144 5.91699 16.2502 5.5812 16.2502 5.16699C16.2502 4.75278 15.9144 4.41699 15.5002 4.41699H12.8335V3.66699C12.8335 3.25278 12.4977 2.91699 12.0835 2.91699H7.91683ZM8.66683 4.41699V4.41699H11.3335V4.41699H8.66683ZM7.23358 5.91699L7.87044 14.0903C7.90039 14.4746 8.22099 14.8337 8.60753 14.8337H11.3929C11.7794 14.8337 12.1 14.4746 12.1299 14.0903L12.7667 5.91699H7.23358Z"
                            fill="" />
                    </svg>
                    Excluir
                </button>
            </form>
        </div>
        </x-ui.modal>
    </div>
@endsection

