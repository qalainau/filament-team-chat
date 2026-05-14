<div class="h-full overflow-y-auto p-4 space-y-1" id="message-feed" wire:poll.{{ config('team-chat.polling.messages', 3) }}s>
    @if($this->messages->isEmpty())
        <div class="flex h-full items-center justify-center">
            <p class="text-gray-400 dark:text-gray-500 text-sm">
                {{ __('team-chat::messages.no_messages') }}
            </p>
        </div>
    @else
        @foreach($this->messages as $message)
            <div class="group flex gap-3 rounded-lg px-2 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800/50" wire:key="message-{{ $message->id }}">
                {{-- Avatar --}}
                <div class="flex-shrink-0 pt-0.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 text-sm font-semibold">
                        {{ strtoupper(substr($message->user->name ?? '?', 0, 1)) }}
                    </div>
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline gap-2">
                        <span class="font-semibold text-sm text-gray-900 dark:text-white">
                            {{ $message->user->name ?? 'Unknown' }}
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            {{ $message->created_at->format('H:i') }}
                        </span>
                        @if($message->isEdited())
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('team-chat::messages.edited') }}</span>
                        @endif

                        {{-- Action bar (visible on hover) --}}
                        <div class="ms-auto flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button
                                wire:click="openThread({{ $message->id }})"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-0.5"
                                title="{{ __('team-chat::messages.reply') }}"
                            >
                                <x-heroicon-o-chat-bubble-left class="h-4 w-4" />
                            </button>
                            <button
                                wire:click="toggleEmojiPicker({{ $message->id }})"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-0.5"
                                title="{{ __('team-chat::messages.react') }}"
                            >
                                <x-heroicon-o-face-smile class="h-4 w-4" />
                            </button>
                            @if($message->user_id === auth()->id())
                                <button
                                    wire:click="startEditing({{ $message->id }})"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-0.5"
                                    title="{{ __('team-chat::messages.edit') }}"
                                >
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                                <button
                                    wire:click="deleteMessage({{ $message->id }})"
                                    wire:confirm="{{ __('team-chat::messages.delete_confirm') }}"
                                    class="text-gray-400 hover:text-red-500 p-0.5"
                                    title="{{ __('team-chat::messages.delete') }}"
                                >
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Message body or edit form --}}
                    @if($editingMessageId === $message->id)
                        <form wire:submit="saveEdit" class="mt-1">
                            <input
                                type="text"
                                wire:model="editBody"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                autofocus
                            />
                            <div class="mt-1 flex gap-2 text-xs">
                                <button type="submit" class="text-primary-600 hover:text-primary-700 font-medium">{{ __('team-chat::messages.save') }}</button>
                                <button type="button" wire:click="cancelEditing" class="text-gray-400 hover:text-gray-600">{{ __('team-chat::messages.cancel') }}</button>
                                <span class="text-gray-400">{{ __('team-chat::messages.edit_hint') }}</span>
                            </div>
                        </form>
                    @else
                        <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                            {!! $message->body_html !!}
                        </div>
                    @endif

                    {{-- Attachments --}}
                    @if($message->attachments->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($message->attachments as $attachment)
                                @if($attachment->isImage())
                                    <a href="{{ $attachment->getUrl() }}" target="_blank" class="block max-w-xs">
                                        <img
                                            src="{{ $attachment->getUrl() }}"
                                            alt="{{ $attachment->file_name }}"
                                            class="rounded-lg border border-gray-200 dark:border-gray-600 max-h-48 object-cover"
                                        />
                                    </a>
                                @else
                                    <a
                                        href="{{ $attachment->getUrl() }}"
                                        target="_blank"
                                        class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <x-heroicon-o-document class="h-5 w-5 text-gray-400 flex-shrink-0" />
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-gray-700 dark:text-gray-300">{{ $attachment->file_name }}</p>
                                            <p class="text-xs text-gray-400">{{ $attachment->getFormattedFileSize() }}</p>
                                        </div>
                                        <x-heroicon-o-arrow-down-tray class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Reactions display --}}
                    @if($message->reactions->isNotEmpty())
                        <div class="mt-1 flex flex-wrap gap-1">
                            @foreach($message->reactions->groupBy('emoji') as $emoji => $reactions)
                                <button
                                    wire:click="addReaction({{ $message->id }}, '{{ $emoji }}')"
                                    @class([
                                        'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs transition-colors',
                                        'border-primary-300 bg-primary-50 text-primary-700 dark:border-primary-600 dark:bg-primary-900/30 dark:text-primary-300' => $reactions->contains('user_id', auth()->id()),
                                        'border-gray-200 bg-gray-50 text-gray-600 hover:border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-500' => ! $reactions->contains('user_id', auth()->id()),
                                    ])
                                >
                                    <span>{{ $emoji }}</span>
                                    <span>{{ $reactions->count() }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Inline emoji picker --}}
                    @if($emojiPickerMessageId === $message->id)
                        <div class="mt-1 flex flex-wrap gap-1 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-2 shadow-sm">
                            @foreach(['👍', '👎', '😄', '🎉', '❤️', '🚀', '👀', '🤔'] as $emoji)
                                <button
                                    wire:click="addReaction({{ $message->id }}, '{{ $emoji }}')"
                                    class="rounded p-1 text-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                >
                                    {{ $emoji }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Thread indicator --}}
                    @if($message->replies_count > 0)
                        <button
                            wire:click="openThread({{ $message->id }})"
                            class="mt-1 flex items-center gap-1 text-xs text-primary-600 dark:text-primary-400 hover:underline"
                        >
                            <x-heroicon-o-chat-bubble-left class="h-3.5 w-3.5" />
                            {{ $message->replies_count }} {{ Str::plural('reply', $message->replies_count) }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</div>
