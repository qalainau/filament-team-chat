<?php

namespace Filament\TeamChat\Livewire;

use Filament\TeamChat\Actions\SendMessage;
use Filament\TeamChat\FilamentTeamChatPlugin;
use Filament\TeamChat\Models\Channel;
use Filament\TeamChat\Models\Conversation;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MessageComposer extends Component
{
    use WithFileUploads;

    public ?string $messageableType = null;

    public ?int $messageableId = null;

    public string $body = '';

    /** @var array<TemporaryUploadedFile> */
    public array $files = [];

    public bool $showMentionSuggestions = false;

    public string $mentionQuery = '';

    public function mount(?string $initialType = null, ?int $initialId = null): void
    {
        if ($initialType === 'channel' && $initialId) {
            $this->messageableType = Channel::class;
            $this->messageableId = $initialId;
        } elseif ($initialType === 'conversation' && $initialId) {
            $this->messageableType = Conversation::class;
            $this->messageableId = $initialId;
        }
    }

    #[On('channel-selected')]
    public function setChannel(int $channelId): void
    {
        $this->messageableType = Channel::class;
        $this->messageableId = $channelId;
        $this->resetComposer();
    }

    #[On('conversation-selected')]
    public function setConversation(int $conversationId): void
    {
        $this->messageableType = Conversation::class;
        $this->messageableId = $conversationId;
        $this->resetComposer();
    }

    public function updatedBody(): void
    {
        if (preg_match('/@(\w*)$/', $this->body, $matches)) {
            $this->mentionQuery = $matches[1];
            $this->showMentionSuggestions = true;
        } else {
            $this->showMentionSuggestions = false;
        }
    }

    public function insertMention(string $name): void
    {
        $this->body = preg_replace('/@\w*$/', '@'.$name.' ', $this->body);
        $this->showMentionSuggestions = false;
    }

    public function removeFile(int $index): void
    {
        array_splice($this->files, $index, 1);
    }

    public function getMentionSuggestionsProperty(): Collection
    {
        if (! $this->showMentionSuggestions) {
            return collect();
        }

        $query = FilamentTeamChatPlugin::get()->getAvailableUsersQuery(auth()->user());

        if ($this->mentionQuery !== '') {
            $query->where('name', 'like', $this->mentionQuery.'%');
        }

        return $query->orderBy('name')->limit(5)->get(['id', 'name']);
    }

    public function sendMessage(): void
    {
        if (! $this->messageableType || ! $this->messageableId) {
            return;
        }

        if (trim($this->body) === '' && empty($this->files)) {
            return;
        }

        $body = trim($this->body) !== '' ? $this->body : '📎 '.__('team-chat::messages.attached_file');

        $messageable = $this->messageableType::findOrFail($this->messageableId);

        app(SendMessage::class)->execute(
            messageable: $messageable,
            userId: auth()->id(),
            body: $body,
            files: $this->files,
        );

        $this->resetComposer();
        $this->dispatch('message-sent');
    }

    protected function resetComposer(): void
    {
        $this->body = '';
        $this->files = [];
        $this->showMentionSuggestions = false;
    }

    public function render()
    {
        return view('team-chat::livewire.message-composer');
    }
}
