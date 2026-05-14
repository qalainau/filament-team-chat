<?php

namespace Filament\TeamChat\Actions;

use Filament\TeamChat\Models\Reaction;

class ToggleReaction
{
    /**
     * Toggle a reaction on a message. Returns true if added, false if removed.
     */
    public function execute(int $messageId, int|string $userId, string $emoji): bool
    {
        $existing = Reaction::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        Reaction::create([
            'message_id' => $messageId,
            'user_id' => $userId,
            'emoji' => $emoji,
        ]);

        return true;
    }
}
