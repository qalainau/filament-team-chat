<?php

namespace Filament\TeamChat\Livewire;

use Filament\TeamChat\Actions\MarkAsRead;
use Filament\TeamChat\Actions\ToggleReaction;
use Filament\TeamChat\Enums\Feature;
use Filament\TeamChat\Models\Channel;
use Filament\TeamChat\Models\Conversation;
use Filament\TeamChat\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\On;
use Livewire\Component;

#[Isolate]
class MessageFeed extends Component
{
    public ?string $messageableType = null;

    public ?int $messageableId = null;

    public int $lastMessageId = 0;

    public function mount(?string $initialType = null, ?int $initialId = null): void
    {
        if ($initialType === 'channel' && $initialId) {
            $this->loadChannel($initialId);
        } elseif ($initialType === 'conversation' && $initialId) {
            $this->loadConversation($initialId);
        }
    }

    #[On('channel-selected')]
    public function loadChannel(int $channelId): void
    {
        $this->messageableType = Channel::class;
        $this->messageableId = $channelId;
        $this->lastMessageId = 0;
    }

    #[On('conversation-selected')]
    public function loadConversation(int $conversationId): void
    {
        $this->messageableType = Conversation::class;
        $this->messageableId = $conversationId;
        $this->lastMessageId = 0;
    }

    #[On('message-sent')]
    public function refreshMessages(): void
    {
        // Triggers re-evaluation of the messages computed property
    }

    public function getMessagesProperty(): Collection
    {
        if (! $this->messageableType || ! $this->messageableId) {
            return collect();
        }

        $messages = Message::where('messageable_type', $this->messageableType)
            ->where('messageable_id', $this->messageableId)
            ->whereNull('parent_id')
            ->with(['user', 'reactions.user', 'attachments'])
            ->withCount('replies')
            ->orderBy('created_at')
            ->get();

        if ($messages->isNotEmpty()) {
            $this->lastMessageId = $messages->last()->id;
            $this->markAsRead();
        }

        return $messages;
    }

    protected function markAsRead(): void
    {
        if (! $this->messageableType || ! $this->messageableId) {
            return;
        }

        $messageable = $this->messageableType::find($this->messageableId);

        if ($messageable) {
            app(MarkAsRead::class)->execute($messageable, auth()->id());
        }
    }

    public ?int $editingMessageId = null;

    public string $editBody = '';

    public ?int $emojiPickerMessageId = null;

    public function toggleEmojiPicker(int $messageId): void
    {
        if (! Feature::Reactions->isEnabled()) {
            return;
        }

        $this->emojiPickerMessageId = $this->emojiPickerMessageId === $messageId ? null : $messageId;
    }

    public function addReaction(int $messageId, string $emoji): void
    {
        abort_unless(Feature::Reactions->isEnabled(), 403);

        app(ToggleReaction::class)->execute($messageId, auth()->id(), $emoji);
        $this->emojiPickerMessageId = null;
    }

    public function startEditing(int $messageId): void
    {
        $message = Message::find($messageId);

        if (! $message || $message->user_id !== auth()->id()) {
            return;
        }

        $this->editingMessageId = $messageId;
        $this->editBody = $message->body;
    }

    public function cancelEditing(): void
    {
        $this->editingMessageId = null;
        $this->editBody = '';
    }

    public function saveEdit(): void
    {
        $message = Message::find($this->editingMessageId);

        if (! $message || $message->user_id !== auth()->id() || trim($this->editBody) === '') {
            return;
        }

        $message->update([
            'body' => $this->editBody,
            'body_html' => Str::markdown($this->editBody),
            'edited_at' => now(),
        ]);

        $this->cancelEditing();
    }

    public function deleteMessage(int $messageId): void
    {
        $message = Message::find($messageId);

        if (! $message || $message->user_id !== auth()->id()) {
            return;
        }

        $message->delete();
    }

    public function openThread(int $messageId): void
    {
        if (! Feature::Threads->isEnabled()) {
            return;
        }

        $this->dispatch('open-thread', messageId: $messageId);
    }

    public function render()
    {
        return view('team-chat::livewire.message-feed');
    }
}
