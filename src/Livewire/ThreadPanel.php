<?php

namespace Filament\TeamChat\Livewire;

use Filament\TeamChat\Actions\SendMessage;
use Filament\TeamChat\Enums\Feature;
use Filament\TeamChat\Models\Message;
use Illuminate\Support\Collection;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Livewire\Component;

#[Isolate]
class ThreadPanel extends Component
{
    public ?int $parentMessageId = null;

    public ?Message $parentMessage = null;

    public string $replyBody = '';

    public function mount(?int $parentMessageId = null): void
    {
        if ($parentMessageId) {
            $this->loadThread($parentMessageId);
        }
    }

    #[On('open-thread')]
    public function loadThread(int $messageId): void
    {
        $this->parentMessageId = $messageId;
        $this->parentMessage = Message::with('user')->find($messageId);
        $this->replyBody = '';
    }

    public function getRepliesProperty(): Collection
    {
        if (! $this->parentMessageId) {
            return collect();
        }

        return Message::where('parent_id', $this->parentMessageId)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    public function sendReply(): void
    {
        if (! Feature::Threads->isEnabled() || ! $this->parentMessage || trim($this->replyBody) === '') {
            return;
        }

        app(SendMessage::class)->execute(
            messageable: $this->parentMessage->messageable,
            userId: auth()->id(),
            body: $this->replyBody,
            parentId: $this->parentMessageId,
        );

        $this->replyBody = '';
        $this->dispatch('message-sent');
    }

    public function render()
    {
        return view('team-chat::livewire.thread-panel');
    }
}
