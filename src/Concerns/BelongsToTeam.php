<?php

namespace Filament\TeamChat\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTeam
{
    public static function bootBelongsToTeam(): void
    {
        if (! config('team-chat.tenancy.enabled')) {
            return;
        }

        static::addGlobalScope('team', function (Builder $query) {
            $teamId = static::resolveCurrentTeamId();

            if ($teamId !== null) {
                $query->where($query->getModel()->getTable().'.team_id', $teamId);
            }
        });

        static::creating(function ($model) {
            if ($model->team_id === null) {
                $model->team_id = static::resolveCurrentTeamId();
            }
        });
    }

    public function team(): BelongsTo
    {
        $model = config('team-chat.tenancy.model');

        return $this->belongsTo($model, 'team_id');
    }

    protected static function resolveCurrentTeamId(): int|string|null
    {
        $resolver = config('team-chat.tenancy.resolver');

        if (is_callable($resolver)) {
            return $resolver();
        }

        if (is_string($resolver) && class_exists($resolver)) {
            return app($resolver)->resolve();
        }

        // Default: try Filament's tenant
        if (class_exists(Filament::class)) {
            $tenant = Filament::getTenant();

            return $tenant?->getKey();
        }

        return null;
    }
}
