<?php

use App\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | All database tables created by this plugin will use this prefix
    | to avoid collisions with the host application's tables.
    |
    */
    'table_prefix' => 'tc_',

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the user model.
    |
    */
    'user_model' => User::class,

    /*
    |--------------------------------------------------------------------------
    | User Key Type
    |--------------------------------------------------------------------------
    |
    | The primary key type of the user model: 'int', 'uuid', or 'ulid'.
    | Used by the migrations to create matching foreign key columns, so set
    | it before running them.
    |
    */
    'user_key_type' => 'int',

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Toggle individual features. Disabling channels, threads, reactions
    | and search turns the chat into a simple 1-on-1 messenger, e.g. for
    | a helpdesk setup.
    |
    */
    'features' => [
        'channels' => true,
        'threads' => true,
        'reactions' => true,
        'search' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Polling Intervals (in seconds)
    |--------------------------------------------------------------------------
    */
    'polling' => [
        'messages' => 3,
        'sidebar' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'disk' => 'public',
        'directory' => 'team-chat-attachments',
        'max_size' => 10240, // KB
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | Enable multi-tenancy to scope channels and conversations per team.
    | When enabled, set the tenant model and the method to resolve the
    | current tenant ID (e.g. from Filament's tenant or auth).
    |
    */
    'tenancy' => [
        'enabled' => false,
        'model' => null, // e.g. \App\Models\Team::class
        'resolver' => null, // callable or class that returns current tenant ID
        'key_type' => 'int', // 'int', 'uuid', or 'ulid' — the tenant primary key type
    ],
];
