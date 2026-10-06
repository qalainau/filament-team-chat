<?php

use Filament\TeamChat\FilamentTeamChatPlugin;
use Filament\TeamChat\Livewire\MessageComposer;
use Filament\TeamChat\Livewire\Sidebar;
use Filament\TeamChat\Livewire\UserProfileCard;
use Filament\TeamChat\Models\Conversation;
use Filament\TeamChat\Tests\Fixtures\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Alice']);
    $this->allowedUser = User::factory()->create(['name' => 'Bob']);
    $this->hiddenUser = User::factory()->create(['name' => 'Carol']);
    $this->actingAs($this->user);
});

function restrictToAllowedUser(User $allowedUser): void
{
    FilamentTeamChatPlugin::get()->modifyAvailableUsersQueryUsing(
        fn (Builder $query) => $query->whereKey($allowedUser->id),
    );
}

it('lists all other users by default', function () {
    $availableUsers = Livewire::test(Sidebar::class)->instance()->availableUsers;

    expect($availableUsers->pluck('id')->all())
        ->toEqualCanonicalizing([$this->allowedUser->id, $this->hiddenUser->id]);
});

it('lists only users allowed by the custom query', function () {
    restrictToAllowedUser($this->allowedUser);

    $availableUsers = Livewire::test(Sidebar::class)->instance()->availableUsers;

    expect($availableUsers->pluck('id')->all())->toBe([$this->allowedUser->id]);
});

it('passes the current user to the custom query callback', function () {
    $receivedUser = null;

    FilamentTeamChatPlugin::get()->modifyAvailableUsersQueryUsing(function (Builder $query, User $user) use (&$receivedUser) {
        $receivedUser = $user;
    });

    Livewire::test(Sidebar::class)->instance()->availableUsers;

    expect($receivedUser->is($this->user))->toBeTrue();
});

it('starts a DM with an allowed user', function () {
    restrictToAllowedUser($this->allowedUser);

    Livewire::test(Sidebar::class)
        ->set('dmUserId', $this->allowedUser->id)
        ->call('startDirectMessage')
        ->assertHasNoErrors()
        ->assertSet('activeType', 'conversation');

    expect($this->user->conversations()->count())->toBe(1);
});

it('refuses to start a DM with a user outside the custom query', function () {
    restrictToAllowedUser($this->allowedUser);

    Livewire::test(Sidebar::class)
        ->set('dmUserId', $this->hiddenUser->id)
        ->call('startDirectMessage')
        ->assertHasErrors('dmUserId');

    expect(Conversation::count())->toBe(0);
});

it('refuses to start a DM with a non-existent user', function () {
    Livewire::test(Sidebar::class)
        ->set('dmUserId', 999)
        ->call('startDirectMessage')
        ->assertHasErrors('dmUserId');

    expect(Conversation::count())->toBe(0);
});

it('hides the send message button and blocks DMs on profiles of unavailable users', function () {
    restrictToAllowedUser($this->allowedUser);

    Livewire::test(UserProfileCard::class)
        ->call('loadProfile', $this->hiddenUser->id)
        ->assertSet('canSendMessage', false)
        ->assertDontSee(__('team-chat::messages.send_message'))
        ->call('startDm');

    expect(Conversation::count())->toBe(0);
});

it('allows DMs from profiles of available users', function () {
    restrictToAllowedUser($this->allowedUser);

    Livewire::test(UserProfileCard::class)
        ->call('loadProfile', $this->allowedUser->id)
        ->assertSet('canSendMessage', true)
        ->call('startDm')
        ->assertDispatched('conversation-selected');

    expect(Conversation::count())->toBe(1);
});

it('limits mention suggestions to available users', function () {
    restrictToAllowedUser($this->allowedUser);

    $suggestions = Livewire::test(MessageComposer::class)
        ->set('body', '@')
        ->instance()
        ->mentionSuggestions;

    expect($suggestions->pluck('id')->all())->toBe([$this->allowedUser->id]);
});
