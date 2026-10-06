<div class="flex h-full w-64 flex-col bg-gray-50 dark:bg-gray-800 border-e border-gray-200 dark:border-gray-700"
     wire:poll.{{ config('team-chat.polling.sidebar', 5) }}s>
    {{-- Workspace Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate">Team Chat</h2>
        <button wire:click="$dispatch('open-search')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="{{ __('team-chat::messages.search') }}">
            <x-heroicon-o-magnifying-glass class="h-5 w-5" />
        </button>
    </div>

    <div class="flex-1 overflow-y-auto py-2">
        {{-- Channels Section --}}
        <div class="flex items-center justify-between px-4 py-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {{ __('team-chat::messages.channels') }}
            </span>
            <div class="flex items-center gap-1">
                <button wire:click="toggleBrowseChannels" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="{{ __('team-chat::messages.browse_channels') }}">
                    <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                </button>
                <button wire:click="toggleCreateChannel" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="{{ __('team-chat::messages.create_channel') }}">
                    <x-heroicon-o-plus class="h-4 w-4" />
                </button>
            </div>
        </div>

        {{-- Create Channel Form --}}
        @if($showCreateChannel)
            <form wire:submit="createChannel" class="px-4 py-2">
                <input
                    type="text"
                    wire:model="newChannelName"
                    placeholder="{{ __('team-chat::messages.channel_name') }}"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    autofocus
                />
                @error('newChannelName')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="mt-2 flex gap-2">
                    <button type="submit" class="rounded-md bg-primary-600 px-3 py-1 text-xs font-medium text-white hover:bg-primary-700">
                        {{ __('team-chat::messages.create') }}
                    </button>
                    <button type="button" wire:click="toggleCreateChannel" class="rounded-md px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        {{ __('team-chat::messages.cancel') }}
                    </button>
                </div>
            </form>
        @endif

        {{-- Channel List --}}
        <nav class="mt-1 space-y-0.5 px-2">
            @foreach($this->channels as $channel)
                @php $unread = $channel->unreadCountFor(auth()->id()); @endphp
                <button
                    wire:click="selectChannel({{ $channel->id }})"
                    @class([
                        'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors',
                        'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' => $activeType === 'channel' && $activeId === $channel->id,
                        'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' => ! ($activeType === 'channel' && $activeId === $channel->id),
                        'font-semibold' => $unread > 0,
                    ])
                >
                    <span class="text-gray-400">#</span>
                    <span class="truncate flex-1 text-start">{{ $channel->name }}</span>
                    @if($unread > 0)
                        <span class="ms-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary-600 px-1.5 text-xs font-medium text-white">
                            {{ $unread > 99 ? '99+' : $unread }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>

        @if($this->channels->isEmpty())
            <p class="px-4 py-2 text-sm text-gray-400 dark:text-gray-500">
                {{ __('team-chat::messages.no_channels') }}
            </p>
        @endif

        {{-- Browse Channels --}}
        @if($showBrowseChannels)
            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">{{ __('team-chat::messages.available_channels') }}</p>
                @forelse($this->browsableChannels as $channel)
                    <div class="flex items-center justify-between py-1">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="text-gray-400">#</span> {{ $channel->name }}
                        </span>
                        <button
                            wire:click="joinChannel({{ $channel->id }})"
                            class="text-xs text-primary-600 hover:text-primary-700 font-medium"
                        >
                            {{ __('team-chat::messages.join') }}
                        </button>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">{{ __('team-chat::messages.no_available_channels') }}</p>
                @endforelse
            </div>
        @endif

        {{-- DM Section --}}
        <div class="mt-4 flex items-center justify-between px-4 py-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {{ __('team-chat::messages.direct_messages') }}
            </span>
            <button wire:click="toggleStartDm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <x-heroicon-o-plus class="h-4 w-4" />
            </button>
        </div>

        {{-- Start DM Form --}}
        @if($showStartDm)
            <form wire:submit="startDirectMessage" class="px-4 py-2">
                <select
                    wire:model="dmUserId"
                    class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="">{{ __('team-chat::messages.select_user') }}</option>
                    @foreach($this->availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('dmUserId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="mt-2 flex gap-2">
                    <button type="submit" class="rounded-md bg-primary-600 px-3 py-1 text-xs font-medium text-white hover:bg-primary-700">
                        {{ __('team-chat::messages.start') }}
                    </button>
                    <button type="button" wire:click="toggleStartDm" class="rounded-md px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        {{ __('team-chat::messages.cancel') }}
                    </button>
                </div>
            </form>
        @endif

        {{-- Conversation List --}}
        <nav class="mt-1 space-y-0.5 px-2">
            @foreach($this->conversations as $conversation)
                @php $unread = $conversation->unreadCountFor(auth()->id()); @endphp
                <button
                    wire:click="selectConversation({{ $conversation->id }})"
                    @class([
                        'flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors',
                        'bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300' => $activeType === 'conversation' && $activeId === $conversation->id,
                        'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' => ! ($activeType === 'conversation' && $activeId === $conversation->id),
                        'font-semibold' => $unread > 0,
                    ])
                >
                    <x-heroicon-o-chat-bubble-oval-left class="h-4 w-4 flex-shrink-0 text-gray-400" />
                    <span class="truncate flex-1 text-start">{{ $conversation->getDisplayNameForUser(auth()->user()) }}</span>
                    @if($unread > 0)
                        <span class="ms-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary-600 px-1.5 text-xs font-medium text-white">
                            {{ $unread > 99 ? '99+' : $unread }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>

        @if($this->conversations->isEmpty() && ! $showStartDm)
            <p class="px-4 py-2 text-sm text-gray-400 dark:text-gray-500">
                {{ __('team-chat::messages.no_dms') }}
            </p>
        @endif
    </div>
</div>
