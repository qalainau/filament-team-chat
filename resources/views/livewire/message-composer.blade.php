<div class="border-t border-gray-200 dark:border-gray-700 p-4">
    @if($messageableId)
        <div class="relative">
            {{-- Mention suggestions --}}
            @if($showMentionSuggestions)
                <div class="absolute bottom-full start-0 mb-1 w-64 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg overflow-hidden z-10">
                    <button
                        wire:click="insertMention('channel')"
                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <span class="text-gray-400">#</span>
                        <span>channel</span>
                        <span class="ms-auto text-xs text-gray-400">{{ __('team-chat::messages.notify_all') }}</span>
                    </button>
                    <button
                        wire:click="insertMention('here')"
                        class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <span class="text-gray-400">@</span>
                        <span>here</span>
                        <span class="ms-auto text-xs text-gray-400">{{ __('team-chat::messages.notify_online') }}</span>
                    </button>
                    @foreach($this->mentionSuggestions as $user)
                        <button
                            wire:click="insertMention('{{ $user->name }}')"
                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <div class="flex h-6 w-6 items-center justify-center rounded-md bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 text-xs font-semibold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span>{{ $user->name }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- File preview --}}
            @if(count($files))
                <div class="mb-2 flex flex-wrap gap-2">
                    @foreach($files as $index => $file)
                        <div class="flex items-center gap-1.5 rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs text-gray-700 dark:text-gray-300">
                            <x-heroicon-o-paper-clip class="h-3.5 w-3.5 text-gray-400" />
                            <span class="max-w-32 truncate">{{ $file->getClientOriginalName() }}</span>
                            <button wire:click="removeFile({{ $index }})" type="button" class="text-gray-400 hover:text-red-500">
                                <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <form wire:submit="sendMessage" class="flex gap-2">
                <label class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <x-heroicon-o-paper-clip class="h-4 w-4" />
                    <input
                        type="file"
                        wire:model="files"
                        multiple
                        class="sr-only"
                    />
                </label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="body"
                    placeholder="{{ __('team-chat::messages.type_message') }}"
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    autocomplete="off"
                />
                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <x-heroicon-o-paper-airplane class="h-4 w-4" />
                </button>
            </form>
        </div>
    @endif
</div>
