<?php

namespace Filament\TeamChat\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class UserProfileCard extends Component
{
    public bool $isOpen = false;

    public int|string|null $userId = null;

    public ?string $displayName = null;

    public ?string $userName = null;

    public ?string $email = null;

    public ?string $statusDisplay = null;

    public bool $isOnline = false;

    #[On('show-profile')]
    public function loadProfile(int|string $userId): void
    {
        $userModel = config('team-chat.user_model');
        $user = $userModel::with('userStatus')->find($userId);

        if (! $user) {
            return;
        }

        $status = $user->userStatus;

        $this->userId = $user->id;
        $this->userName = $user->name;
        $this->email = $user->email;
        $this->displayName = $status?->getDisplayName() ?? $user->name;
        $this->statusDisplay = $status?->getStatusDisplay();
        $this->isOnline = $status?->is_online ?? false;
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function startDm(): void
    {
        if (! $this->userId) {
            return;
        }

        $conversation = auth()->user()->findOrCreateDirectMessage($this->userId);
        $this->dispatch('conversation-selected', conversationId: $conversation->id);
        $this->close();
    }

    public function render()
    {
        return view('team-chat::livewire.user-profile-card');
    }
}
