<?php

namespace Filament\TeamChat;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\TeamChat\Pages\TeamChat;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;

class FilamentTeamChatPlugin implements Plugin
{
    protected ?Closure $modifyAvailableUsersQueryUsing = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        if ($panel?->hasPlugin('team-chat')) {
            return $panel->getPlugin('team-chat');
        }

        return static::make();
    }

    public function getId(): string
    {
        return 'team-chat';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            TeamChat::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Restrict which users can be found and messaged directly.
     *
     * The callback receives the users query (already excluding the current user)
     * and the current user, and may modify the query or return a new one.
     *
     * @param  Closure(Builder, Authenticatable): (Builder|null)  $callback
     */
    public function modifyAvailableUsersQueryUsing(?Closure $callback): static
    {
        $this->modifyAvailableUsersQueryUsing = $callback;

        return $this;
    }

    public function getAvailableUsersQuery(Authenticatable $user): Builder
    {
        $userModel = config('team-chat.user_model');

        $query = $userModel::query()->whereKeyNot($user->getAuthIdentifier());

        if ($this->modifyAvailableUsersQueryUsing) {
            $query = ($this->modifyAvailableUsersQueryUsing)($query, $user) ?? $query;
        }

        return $query;
    }

    public function canMessageUser(Authenticatable $user, int|string $otherUserId): bool
    {
        return $this->getAvailableUsersQuery($user)->whereKey($otherUserId)->exists();
    }
}
