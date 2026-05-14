<?php

namespace Filament\TeamChat\Actions;

use Filament\TeamChat\Models\Channel;
use Filament\TeamChat\Models\Conversation;
use Filament\TeamChat\Models\Message;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SearchMessages
{
    public function execute(int|string $userId, string $query, int $limit = 20): Collection
    {
        if (trim($query) === '') {
            return collect();
        }

        $userModel = config('team-chat.user_model');
        $user = $userModel::find($userId);

        if (! $user) {
            return collect();
        }

        $channelIds = $user->channels()->pluck('tc_channels.id');
        $conversationIds = $user->conversations()->pluck('tc_conversations.id');

        return Message::where('body', 'like', '%'.trim($query).'%')
            ->where(function (Builder $q) use ($channelIds, $conversationIds) {
                $q->where(function (Builder $q) use ($channelIds) {
                    $q->where('messageable_type', Channel::class)
                        ->whereIn('messageable_id', $channelIds);
                })->orWhere(function (Builder $q) use ($conversationIds) {
                    $q->where('messageable_type', Conversation::class)
                        ->whereIn('messageable_id', $conversationIds);
                });
            })
            ->with(['user', 'messageable'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
