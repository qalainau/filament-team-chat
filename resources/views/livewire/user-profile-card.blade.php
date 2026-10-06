<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-on:keydown.escape.window="$wire.close()">
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>

            <div class="relative w-full max-w-sm rounded-xl bg-white dark:bg-gray-800 shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-primary-600 px-6 py-8 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/20 text-2xl font-bold text-white">
                        {{ strtoupper(substr($displayName ?? '?', 0, 1)) }}
                    </div>
                    <h3 class="mt-3 text-lg font-semibold text-white">{{ $displayName }}</h3>
                    @if($statusDisplay)
                        <p class="mt-1 text-sm text-primary-100">{{ $statusDisplay }}</p>
                    @endif
                    <div class="mt-2 flex items-center justify-center gap-1.5">
                        <span @class([
                            'h-2.5 w-2.5 rounded-full',
                            'bg-green-400' => $isOnline,
                            'bg-gray-400' => ! $isOnline,
                        ])></span>
                        <span class="text-xs text-primary-100">
                            {{ $isOnline ? __('team-chat::messages.online') : __('team-chat::messages.offline') }}
                        </span>
                    </div>
                </div>

                {{-- Info --}}
                <div class="px-6 py-4 space-y-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('team-chat::messages.username') }}</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $userName }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('team-chat::messages.email') }}</p>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $email }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3 flex gap-2">
                    @if($canSendMessage)
                        <button
                            wire:click="startDm"
                            class="flex-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
                        >
                            {{ __('team-chat::messages.send_message') }}
                        </button>
                    @endif
                    <button
                        wire:click="close"
                        class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        {{ __('team-chat::messages.close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
