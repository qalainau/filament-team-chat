<x-filament-panels::page>
    <style>
        .fi-page-header { display: none !important; }
    </style>

    <div
        x-data="{ height: 0 }"
        x-init="
            const update = () => { height = window.innerHeight - $el.getBoundingClientRect().top - 16 };
            update();
            window.addEventListener('resize', update);
        "
        :style="'height: ' + height + 'px'"
        class="tc-chat-container flex overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
    >
        {{-- Chat Sidebar --}}
        <livewire:team-chat::sidebar :active-type="$activeType" :active-id="$activeId" :wire:key="'sidebar'" />

        {{-- Main content area --}}
        <div class="flex flex-1 flex-col min-w-0">
            @if($activeId)
                {{-- Channel/Conversation Header --}}
                <div class="shrink-0">
                    <livewire:team-chat::channel-header :initial-type="$activeType" :initial-id="$activeId" :wire:key="'header-'.$activeId" />
                </div>

                {{-- Message Feed --}}
                <div class="flex-1 min-h-0 overflow-hidden">
                    <livewire:team-chat::message-feed
                        :initial-type="$activeType"
                        :initial-id="$activeId"
                        :wire:key="'feed-'.$activeId"
                    />
                </div>

                {{-- Message Composer --}}
                <div class="shrink-0">
                    <livewire:team-chat::message-composer :initial-type="$activeType" :initial-id="$activeId" :wire:key="'composer-'.$activeId" />
                </div>
            @else
                <div class="flex flex-1 items-center justify-center text-gray-400 dark:text-gray-500">
                    <div class="text-center">
                        <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 mb-4" />
                        <p class="text-lg font-medium">{{ __('team-chat::messages.select_channel_or_dm') }}</p>
                        <p class="text-sm mt-1">{{ __('team-chat::messages.select_channel_hint') }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Thread Panel --}}
        @if($showThreadPanel && $threadParentId)
            <div class="w-96 border-s border-gray-200 dark:border-gray-700 flex flex-col bg-white dark:bg-gray-900">
                <livewire:team-chat::thread-panel :parent-message-id="$threadParentId" :wire:key="'thread-'.$threadParentId" />
            </div>
        @endif
    </div>

    {{-- Search Modal --}}
    <livewire:team-chat::search-modal :wire:key="'search'" />

    {{-- Member List Modal --}}
    <livewire:team-chat::member-list :wire:key="'members'" />

    {{-- User Profile Card Modal --}}
    <livewire:team-chat::user-profile-card :wire:key="'profile'" />
</x-filament-panels::page>
