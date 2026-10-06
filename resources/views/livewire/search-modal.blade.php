<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-start justify-center pt-20" x-data x-on:keydown.escape.window="$wire.close()">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50" wire:click="close"></div>

            {{-- Modal --}}
            <div class="relative w-full max-w-xl rounded-xl bg-white dark:bg-gray-800 shadow-2xl overflow-hidden">
                {{-- Search input --}}
                <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400 flex-shrink-0" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="query"
                        placeholder="{{ __('team-chat::messages.search_messages') }}"
                        class="flex-1 border-0 bg-transparent text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0"
                        autofocus
                    />
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>

                {{-- Results --}}
                <div class="max-h-96 overflow-y-auto">
                    @if(strlen(trim($query)) < 2)
                        <p class="p-4 text-center text-sm text-gray-400">
                            {{ __('team-chat::messages.search_min_chars') }}
                        </p>
                    @elseif($this->results->isEmpty())
                        <p class="p-4 text-center text-sm text-gray-400">
                            {{ __('team-chat::messages.search_no_results', ['query' => $query]) }}
                        </p>
                    @else
                        @foreach($this->results as $message)
                            <button
                                wire:click="goToMessage({{ $message->id }}, '{{ addslashes($message->messageable_type) }}', {{ $message->messageable_id }})"
                                class="flex w-full gap-3 px-4 py-3 text-start hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                wire:key="search-{{ $message->id }}"
                            >
                                <div class="flex-shrink-0 pt-0.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 text-xs font-semibold">
                                        {{ strtoupper(substr($message->user->name ?? '?', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $message->user->name ?? 'Unknown' }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $message->created_at->format('m/d H:i') }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            @if($message->messageable_type === 'Filament\\TeamChat\\Models\\Channel')
                                                #{{ $message->messageable->name ?? '' }}
                                            @else
                                                DM
                                            @endif
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $message->body }}
                                    </p>
                                </div>
                            </button>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
