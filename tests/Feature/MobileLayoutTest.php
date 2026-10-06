<?php

use Filament\TeamChat\Livewire\ChannelHeader;
use Filament\TeamChat\Models\Channel;
use Filament\TeamChat\Pages\TeamChat;
use Filament\TeamChat\Tests\Fixtures\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->actingAs($this->user);

    $this->channel = Channel::create([
        'name' => 'general',
        'slug' => 'general',
        'type' => 'public',
        'created_by' => $this->user->id,
    ]);
    $this->channel->members()->attach($this->user->id, ['role' => 'owner']);
});

it('shows the sidebar on mobile initially', function () {
    Livewire::test(TeamChat::class)
        ->assertSet('showSidebarOnMobile', true);
});

it('switches to the chat on mobile when a channel or conversation is selected', function () {
    $conversation = $this->user->findOrCreateDirectMessage($this->otherUser->id);

    Livewire::test(TeamChat::class)
        ->dispatch('channel-selected', channelId: $this->channel->id)
        ->assertSet('showSidebarOnMobile', false)
        ->call('showSidebar')
        ->assertSet('showSidebarOnMobile', true)
        ->dispatch('conversation-selected', conversationId: $conversation->id)
        ->assertSet('showSidebarOnMobile', false);
});

it('goes back to the sidebar when the header back button is used', function () {
    Livewire::test(TeamChat::class)
        ->dispatch('channel-selected', channelId: $this->channel->id)
        ->dispatch('show-chat-sidebar')
        ->assertSet('showSidebarOnMobile', true);
});

it('renders a back button in the channel header', function () {
    Livewire::test(ChannelHeader::class, ['initialType' => 'channel', 'initialId' => $this->channel->id])
        ->assertSeeHtml("\$dispatch('show-chat-sidebar')")
        ->assertSee(__('team-chat::messages.back'));
});
