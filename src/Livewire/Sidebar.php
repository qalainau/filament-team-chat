<?php

namespace Filament\TeamChat\Livewire;

use Filament\TeamChat\Models\Channel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Livewire\Component;

#[Isolate]
class Sidebar extends Component
{
    public ?string $activeType = null;

    public ?int $activeId = null;

    public string $newChannelName = '';

    public bool $showCreateChannel = false;

    public bool $showStartDm = false;

    public int|string|null $dmUserId = null;

    public function selectChannel(int $channelId): void
    {
        $channel = Channel::findOrFail($channelId);

        // Auto-join public channels on first access
        if ($channel->isPublic() && ! $channel->members()->where('user_id', auth()->id())->exists()) {
            $channel->members()->attach(auth()->id(), ['role' => 'member']);
        }

        $this->activeType = 'channel';
        $this->activeId = $channelId;
        $this->dispatch('channel-selected', channelId: $channelId);
    }

    #[On('message-sent')]
    #[On('channel-updated')]
    public function refreshSidebar(): void
    {
        // Triggers re-render to update unread badges and channel names
    }

    public function selectConversation(int $conversationId): void
    {
        $this->activeType = 'conversation';
        $this->activeId = $conversationId;
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    public function toggleCreateChannel(): void
    {
        $this->showCreateChannel = ! $this->showCreateChannel;
        $this->newChannelName = '';
    }

    public function createChannel(): void
    {
        $this->validate([
            'newChannelName' => 'required|string|max:255',
        ]);

        $channel = Channel::create([
            'name' => $this->newChannelName,
            'slug' => Str::slug($this->newChannelName),
            'type' => 'public',
            'created_by' => auth()->id(),
        ]);

        $channel->members()->attach(auth()->id(), [
            'role' => 'owner',
        ]);

        $this->selectChannel($channel->id);
        $this->showCreateChannel = false;
        $this->newChannelName = '';
    }

    public function toggleStartDm(): void
    {
        $this->showStartDm = ! $this->showStartDm;
        $this->dmUserId = null;
    }

    public function startDirectMessage(): void
    {
        $this->validate([
            'dmUserId' => 'required|exists:users,id',
        ]);

        $conversation = auth()->user()->findOrCreateDirectMessage($this->dmUserId);

        $this->selectConversation($conversation->id);
        $this->showStartDm = false;
        $this->dmUserId = null;
    }

    public bool $showBrowseChannels = false;

    public function toggleBrowseChannels(): void
    {
        $this->showBrowseChannels = ! $this->showBrowseChannels;
    }

    public function joinChannel(int $channelId): void
    {
        $channel = Channel::where('type', 'public')
            ->whereNull('archived_at')
            ->findOrFail($channelId);

        $channel->members()->syncWithoutDetaching([auth()->id() => ['role' => 'member']]);

        $this->selectChannel($channel->id);
        $this->showBrowseChannels = false;
    }

    public function leaveChannel(int $channelId): void
    {
        auth()->user()->channels()->detach($channelId);

        if ($this->activeType === 'channel' && $this->activeId === $channelId) {
            $this->activeType = null;
            $this->activeId = null;
        }
    }

    public function getChannelsProperty(): Collection
    {
        $joinedChannels = auth()->user()->channels()->whereNull('archived_at')->get();

        $publicChannels = Channel::where('type', 'public')
            ->whereNull('archived_at')
            ->whereNotIn('id', $joinedChannels->pluck('id'))
            ->get();

        return $joinedChannels->merge($publicChannels)->sortBy('name')->values();
    }

    public function getBrowsableChannelsProperty(): Collection
    {
        $joinedIds = auth()->user()->channels()->pluck('tc_channels.id');

        return Channel::where('type', 'public')
            ->whereNull('archived_at')
            ->whereNotIn('id', $joinedIds)
            ->orderBy('name')
            ->get();
    }

    public function getConversationsProperty(): Collection
    {
        return auth()->user()->conversations()->with('participants')->latest('tc_conversations.updated_at')->get();
    }

    public function getAvailableUsersProperty(): Collection
    {
        $userModel = config('team-chat.user_model');

        return $userModel::where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        return view('team-chat::livewire.sidebar');
    }
}
