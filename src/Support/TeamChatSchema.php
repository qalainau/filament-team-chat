<?php

namespace Filament\TeamChat\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignIdColumnDefinition;

class TeamChatSchema
{
    /**
     * Add a foreign key column referencing the user model, matching its key type.
     */
    public static function userForeignId(Blueprint $table, string $column = 'user_id'): ForeignIdColumnDefinition
    {
        return match (config('team-chat.user_key_type', 'int')) {
            'uuid' => $table->foreignUuid($column),
            'ulid' => $table->foreignUlid($column),
            default => $table->foreignId($column),
        };
    }

    /**
     * Get the table name of the configured user model.
     */
    public static function userTable(): string
    {
        $userModel = config('team-chat.user_model');

        return (new $userModel)->getTable();
    }

    /**
     * Add the nullable, indexed team_id column, matching the tenant key type.
     */
    public static function teamId(Blueprint $table): ColumnDefinition
    {
        $column = match (config('team-chat.tenancy.key_type', 'int')) {
            'uuid' => $table->uuid('team_id'),
            'ulid' => $table->ulid('team_id'),
            default => $table->unsignedBigInteger('team_id'),
        };

        return $column->nullable()->index();
    }
}
