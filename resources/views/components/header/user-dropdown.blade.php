@php
    $user = auth()->user();
    $displayName = $user->name ?? 'User';
    $parts = preg_split('/\s+/', trim($displayName), -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) >= 2) {
        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    } else {
        $initials = mb_strtoupper(mb_substr($displayName, 0, min(2, mb_strlen($displayName))));
    }
    if ($initials === '') {
        $initials = 'U';
    }
@endphp
<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- User Button -->
    <button
        class="flex items-center text-gray-700 dark:text-gray-400"
        @click.prevent="toggleDropdown()"
        type="button"
    >
        <span
            class="mr-3 flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-500 text-sm font-semibold text-white dark:bg-brand-600"
            aria-hidden="true"
        >
            {{ $initials }}
        </span>

       <span class="block mr-1 font-medium text-theme-sm">{{ $displayName }}</span>

        <!-- Chevron Icon -->
        <svg
            class="w-5 h-5 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        <div class="pb-3">
            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">{{ $displayName }}</span>
            <span class="mt-1 block text-theme-xs font-medium text-gray-500 dark:text-gray-500">Login</span>
            <span class="mt-0.5 block text-theme-xs text-gray-600 dark:text-gray-400">{{ $user->email ?? '—' }}</span>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200 pt-3 dark:border-gray-800">
            @csrf
            <button
                type="submit"
                class="flex items-center w-full gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                @click="closeDropdown()"
            >
                <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </span>
                Sair
            </button>
        </form>
    </div>
    <!-- Dropdown End -->
</div>
