<?php

namespace Filament\TeamChat\Actions;

use Filament\TeamChat\Models\Message;
use Filament\TeamChat\Models\ReadReceipt;
use Illuminate\Database\Eloquent\Model;

class MarkAsRead
{
    public function execute(Model $readable, int|string $userId): void
    {
        $lastMessage = Message::where('messageable_type', $readable->getMorphClass())
            ->where('messageable_id', $readable->getKey())
            ->whereNull('parent_id')
            ->latest('id')
            ->first();

        if (! $lastMessage) {
            return;
        }

        ReadReceipt::updateOrCreate(
            [
                'readable_type' => $readable->getMorphClass(),
                'readable_id' => $readable->getKey(),
                'user_id' => $userId,
            ],
            [
                'last_read_message_id' => $lastMessage->id,
                'read_at' => now(),
            ],
        );
    }
}
