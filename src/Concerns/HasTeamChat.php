<?php

namespace Filament\TeamChat\Concerns;

use Filament\TeamChat\Models\Channel;
use Filament\TeamChat\Models\Conversation;
use Filament\TeamChat\Models\Message;
use Filament\TeamChat\Models\ReadReceipt;
use Filament\TeamChat\Models\UserStatus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasTeamChat
{
    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(Channel::class, 'tc_channel_user')
            ->withPivot(['role', 'is_muted', 'joined_at'])
            ->withTimestamps();
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'tc_conversation_user')
            ->withPivot(['is_muted'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(ReadReceipt::class);
    }

    public function userStatus(): HasOne
    {
        return $this->hasOne(UserStatus::class);
    }

    public function getOrCreateStatus(): UserStatus
    {
        return $this->userStatus ?? $this->userStatus()->create()->fresh();
    }

    public function touchOnline(): void
    {
        $this->getOrCreateStatus()->markOnline();
    }

    /**
     * Find or create a 1-on-1 DM with another user.
     */
    public function findOrCreateDirectMessage(int|string $otherUserId): Conversation
    {
        $existing = $this->conversations()
            ->where('is_group', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $otherUserId))
            ->first();

        if ($existing) {
            return $existing;
        }

        $conversation = Conversation::create(['is_group' => false]);

        $userIds = array_unique([$this->getKey(), $otherUserId]);
        $conversation->participants()->attach($userIds);

        return $conversation;
    }

    /**
     * Create a group DM with multiple users.
     */
    public function createGroupConversation(array $userIds, ?string $name = null): Conversation
    {
        $conversation = Conversation::create([
            'is_group' => true,
            'name' => $name,
        ]);

        $allUserIds = collect($userIds)->push($this->getKey())->unique()->all();
        $conversation->participants()->attach($allUserIds);

        return $conversation;
    }
}
