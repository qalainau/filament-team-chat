<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data x-on:keydown.escape.window="$wire.close()">
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>

            <div class="relative w-full max-w-sm rounded-xl bg-white dark:bg-gray-800 shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('team-chat::messages.members') }}</h3>
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($this->members as $member)
                        <button
                            wire:click="showProfile({{ $member->id }})"
                            class="flex w-full items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <div class="relative">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 text-sm font-semibold">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <span @class([
                                    'absolute -bottom-0.5 -end-0.5 h-3 w-3 rounded-full border-2 border-white dark:border-gray-800',
                                    'bg-green-400' => $member->userStatus?->is_online,
                                    'bg-gray-300 dark:bg-gray-600' => ! $member->userStatus?->is_online,
                                ])></span>
                            </div>
                            <div class="min-w-0 flex-1 text-start">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $member->userStatus?->display_name ?? $member->name }}
                                </p>
                                @if($member->userStatus?->getStatusDisplay())
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $member->userStatus->getStatusDisplay() }}
                                    </p>
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
