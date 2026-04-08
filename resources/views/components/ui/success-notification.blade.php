@props([
    'title' => 'Sucesso',
    'message' => '',
    'duration' => 4000,
])

<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, {{ (int) $duration }})"
    x-show="show"
    x-transition:enter="transform ease-out duration-300"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed right-4 top-4 z-[99999] w-full"
    role="status"
    aria-live="polite"
>
    <div class="flex items-center justify-end gap-3 border-t border-gray-100 p-4 sm:p-6 dark:border-gray-800">
        <div class="flex w-full items-center justify-between gap-3 rounded-md border-b-4 border-success-500 bg-white p-3 shadow-theme-sm sm:max-w-[340px] dark:bg-[#1E2634]">
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success-50 text-success-600 dark:bg-success-500/[0.15] dark:text-success-500">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M3.55078 12C3.55078 7.33417 7.3332 3.55176 11.999 3.55176C16.6649 3.55176 20.4473 7.33417 20.4473 12C20.4473 16.6659 16.6649 20.4483 11.999 20.4483C7.3332 20.4483 3.55078 16.6659 3.55078 12ZM11.999 2.05176C6.50477 2.05176 2.05078 6.50574 2.05078 12C2.05078 17.4943 6.50477 21.9483 11.999 21.9483C17.4933 21.9483 21.9473 17.4943 21.9473 12C21.9473 6.50574 17.4933 2.05176 11.999 2.05176ZM15.5126 10.6333C15.8055 10.3405 15.8055 9.86558 15.5126 9.57269C15.2197 9.27979 14.7448 9.27979 14.4519 9.57269L11.1883 12.8364L9.54616 11.1942C9.25327 10.9014 8.7784 10.9014 8.4855 11.1942C8.19261 11.4871 8.19261 11.962 8.4855 12.2549L10.6579 14.4273C10.7986 14.568 10.9894 14.647 11.1883 14.647C11.3872 14.647 11.578 14.568 11.7186 14.4273L15.5126 10.6333Z"
                            fill="currentColor" />
                    </svg>
                </div>

                <div>
                    <h4 class="text-sm text-gray-800 sm:text-base dark:text-white/90">{{ $title }}</h4>
                    @if ($message)
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $message }}</p>
                    @endif
                </div>
            </div>

            <button
                type="button"
                @click="show = false"
                class="text-gray-400 hover:text-gray-800 dark:hover:text-white/90"
                aria-label="Fechar notificação"
            >
                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M6.04289 16.5418C5.65237 16.9323 5.65237 17.5655 6.04289 17.956C6.43342 18.3465 7.06658 18.3465 7.45711 17.956L11.9987 13.4144L16.5408 17.9565C16.9313 18.347 17.5645 18.347 17.955 17.9565C18.3455 17.566 18.3455 16.9328 17.955 16.5423L13.4129 12.0002L17.955 7.45808C18.3455 7.06756 18.3455 6.43439 17.955 6.04387C17.5645 5.65335 16.9313 5.65335 16.5408 6.04387L11.9987 10.586L7.45711 6.04439C7.06658 5.65386 6.43342 5.65386 6.04289 6.04439C5.65237 6.43491 5.65237 7.06808 6.04289 7.4586L10.5845 12.0002L6.04289 16.5418Z"
                        fill="currentColor" />
                </svg>
            </button>
        </div>
    </div>
</div>
