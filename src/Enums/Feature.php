<?php

namespace Filament\TeamChat\Enums;

enum Feature: string
{
    case Channels = 'channels';
    case Threads = 'threads';
    case Reactions = 'reactions';
    case Search = 'search';

    public function isEnabled(): bool
    {
        return (bool) config("team-chat.features.{$this->value}", true);
    }
}
