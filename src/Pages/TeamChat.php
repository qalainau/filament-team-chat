<?php

namespace Filament\TeamChat\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\TeamChat\Enums\Feature;
use Livewire\Attributes\On;

class TeamChat extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Team Chat';

    protected static ?string $title = 'Team Chat';

    protected static ?string $slug = 'team-chat';

    protected static ?int $navigationSort = 1;

    protected string $view = 'team-chat::pages.team-chat';

    protected ?string $heading = '';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public ?string $activeType = null; // 'channel' or 'conversation'

    public ?int $activeId = null;

    public bool $showThreadPanel = false;

    public ?int $threadParentId = null;

    /**
     * Whether the sidebar (instead of the chat) is shown on small screens.
     */
    public bool $showSidebarOnMobile = true;

    public function mount(): void
    {
        auth()->user()->touchOnline();

        $channel = Feature::Channels->isEnabled() ? auth()->user()->channels()->first() : null;

        if ($channel) {
            $this->activeType = 'channel';
            $this->activeId = $channel->id;
        }
    }

    #[On('channel-selected')]
    public function selectChannel(int $channelId): void
    {
        $this->activeType = 'channel';
        $this->activeId = $channelId;
        $this->showSidebarOnMobile = false;
        $this->showThreadPanel = false;
        $this->threadParentId = null;
    }

    #[On('conversation-selected')]
    public function selectConversation(int $conversationId): void
    {
        $this->activeType = 'conversation';
        $this->activeId = $conversationId;
        $this->showSidebarOnMobile = false;
        $this->showThreadPanel = false;
        $this->threadParentId = null;
    }

    #[On('open-thread')]
    public function openThread(int $messageId): void
    {
        if (! Feature::Threads->isEnabled()) {
            return;
        }

        $this->threadParentId = $messageId;
        $this->showThreadPanel = true;
    }

    #[On('close-thread')]
    public function closeThread(): void
    {
        $this->showThreadPanel = false;
        $this->threadParentId = null;
    }

    #[On('show-chat-sidebar')]
    public function showSidebar(): void
    {
        $this->showSidebarOnMobile = true;
    }
}
