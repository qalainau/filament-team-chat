<?php

use Filament\TeamChat\Livewire\MessageFeed;
use Filament\TeamChat\Livewire\SearchModal;
use Filament\TeamChat\Livewire\Sidebar;
use Filament\TeamChat\Models\Channel;
use Filament\TeamChat\Models\Message;
use Filament\TeamChat\Models\Reaction;
use Filament\TeamChat\Pages\TeamChat;
use Filament\TeamChat\Tests\Fixtures\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->channel = Channel::create([
        'name' => 'general',
        'slug' => 'general',
        'type' => 'public',
        'created_by' => $this->user->id,
    ]);
    $this->channel->members()->attach($this->user->id, ['role' => 'owner']);
});

it('shows channels when the channels feature is enabled', function () {
    Livewire::test(Sidebar::class)
        ->assertSee(__('team-chat::messages.channels'))
        ->assertSee('general');
});

it('hides channels when the channels feature is disabled', function () {
    config(['team-chat.features.channels' => false]);

    $sidebar = Livewire::test(Sidebar::class)
        ->assertDontSee(__('team-chat::messages.channels'))
        ->assertDontSee('general')
        ->assertSee(__('team-chat::messages.direct_messages'));

    expect($sidebar->instance()->channels)->toBeEmpty();
});

it('blocks channel actions when the channels feature is disabled', function (string $method, array $arguments) {
    config(['team-chat.features.channels' => false]);

    Livewire::test(Sidebar::class)
        ->set('newChannelName', 'new-channel')
        ->call($method, ...$arguments)
        ->assertForbidden();
})->with([
    'select' => fn () => ['selectChannel', [$this->channel->id]],
    'join' => fn () => ['joinChannel', [$this->channel->id]],
    'create' => fn () => ['createChannel', []],
]);

it('does not auto-select a channel on the page when channels are disabled', function () {
    config(['team-chat.features.channels' => false]);

    Livewire::test(TeamChat::class)
        ->assertSet('activeId', null);
});

it('hides threads when the threads feature is disabled', function () {
    config(['team-chat.features.threads' => false]);

    $message = Message::factory()->create([
        'messageable_type' => Channel::class,
        'messageable_id' => $this->channel->id,
        'user_id' => $this->user->id,
    ]);

    Livewire::test(MessageFeed::class, ['initialType' => 'channel', 'initialId' => $this->channel->id])
        ->assertDontSee(__('team-chat::messages.reply'))
        ->call('openThread', $message->id)
        ->assertNotDispatched('open-thread');

    Livewire::test(TeamChat::class)
        ->call('openThread', $message->id)
        ->assertSet('showThreadPanel', false);
});

it('hides reactions when the reactions feature is disabled', function () {
    config(['team-chat.features.reactions' => false]);

    $message = Message::factory()->create([
        'messageable_type' => Channel::class,
        'messageable_id' => $this->channel->id,
        'user_id' => $this->user->id,
    ]);

    Livewire::test(MessageFeed::class, ['initialType' => 'channel', 'initialId' => $this->channel->id])
        ->assertDontSee(__('team-chat::messages.react'))
        ->call('addReaction', $message->id, '👍')
        ->assertForbidden();

    expect(Reaction::count())->toBe(0);
});

it('disables search when the search feature is disabled', function () {
    config(['team-chat.features.search' => false]);

    Livewire::test(Sidebar::class)
        ->assertDontSee(__('team-chat::messages.search'));

    Livewire::test(SearchModal::class)
        ->dispatch('open-search')
        ->assertSet('isOpen', false);
});
