<?php

namespace Filament\TeamChat\Livewire;

use Filament\TeamChat\Models\Channel;
use Filament\TeamChat\Models\Conversation;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class MemberList extends Component
{
    public bool $isOpen = false;

    public ?string $messageableType = null;

    public ?int $messageableId = null;

    #[On('show-members')]
    public function open(string $type, int $id): void
    {
        $this->messageableType = $type;
        $this->messageableId = $id;
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function getMembersProperty(): Collection
    {
        if (! $this->messageableType || ! $this->messageableId) {
            return collect();
        }

        if ($this->messageableType === 'channel') {
            $channel = Channel::find($this->messageableId);

            return $channel?->members()->with('userStatus')->orderBy('name')->get() ?? collect();
        }

        $conversation = Conversation::find($this->messageableId);

        return $conversation?->participants()->with('userStatus')->orderBy('name')->get() ?? collect();
    }

    public function showProfile(int|string $userId): void
    {
        $this->dispatch('show-profile', userId: $userId);
    }

    public function render()
    {
        return view('team-chat::livewire.member-list');
    }
}
