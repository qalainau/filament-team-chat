<?php

use Filament\TeamChat\Support\TeamChatSchema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

afterEach(function () {
    Schema::dropIfExists('tc_schema_test');
});

it('creates user and team columns matching the configured key types', function (string $keyType, array $expectedTypes) {
    config([
        'team-chat.user_key_type' => $keyType,
        'team-chat.tenancy.key_type' => $keyType,
    ]);

    Schema::create('tc_schema_test', function (Blueprint $table) {
        TeamChatSchema::userForeignId($table);
        TeamChatSchema::teamId($table);
    });

    expect(Schema::getColumnType('tc_schema_test', 'user_id'))->toBeIn($expectedTypes)
        ->and(Schema::getColumnType('tc_schema_test', 'team_id'))->toBeIn($expectedTypes);
})->with([
    'int' => ['int', ['integer', 'bigint']],
    'uuid' => ['uuid', ['varchar', 'char', 'uuid']],
    'ulid' => ['ulid', ['varchar', 'char']],
]);

it('uses the table of the configured user model', function () {
    expect(TeamChatSchema::userTable())->toBe('users');
});
