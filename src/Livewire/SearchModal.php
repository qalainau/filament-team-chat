<?php

namespace Filament\TeamChat\Livewire;

use Filament\TeamChat\Actions\SearchMessages;
use Filament\TeamChat\Enums\Feature;
use Filament\TeamChat\Models\Channel;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class SearchModal extends Component
{
    public bool $isOpen = false;

    public string $query = '';

    #[On('open-search')]
    public function open(): void
    {
        if (! Feature::Search->isEnabled()) {
            return;
        }

        $this->isOpen = true;
        $this->query = '';
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
    }

    public function getResultsProperty(): Collection
    {
        if (! Feature::Search->isEnabled() || strlen(trim($this->query)) < 2) {
            return collect();
        }

        return app(SearchMessages::class)->execute(auth()->id(), $this->query);
    }

    public function goToMessage(int $messageId, string $messageableType, int $messageableId): void
    {
        if ($messageableType === Channel::class) {
            $this->dispatch('channel-selected', channelId: $messageableId);
        } else {
            $this->dispatch('conversation-selected', conversationId: $messageableId);
        }

        $this->close();
    }

    public function render()
    {
        return view('team-chat::livewire.search-modal');
    }
}
