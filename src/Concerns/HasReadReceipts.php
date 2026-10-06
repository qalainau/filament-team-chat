<?php

namespace Filament\TeamChat\Concerns;

use Filament\TeamChat\Models\Message;
use Filament\TeamChat\Models\ReadReceipt;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasReadReceipts
{
    public function readReceipts(): MorphMany
    {
        return $this->morphMany(ReadReceipt::class, 'readable');
    }

    public function unreadCountFor(int|string $userId): int
    {
        $receipt = $this->readReceipts()->where('user_id', $userId)->first();

        $query = Message::where('messageable_type', $this->getMorphClass())
            ->where('messageable_id', $this->getKey())
            ->whereNull('parent_id');

        if ($receipt) {
            $query->where('id', '>', $receipt->last_read_message_id);
        }

        return $query->count();
    }

    public function getLastReadMessageIdFor(int|string $userId): ?int
    {
        return $this->readReceipts()
            ->where('user_id', $userId)
            ->value('last_read_message_id');
    }
}
