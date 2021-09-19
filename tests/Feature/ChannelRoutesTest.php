<?php

namespace Tests\Feature;

use App\Models\{
    User,
    Space,
    SpaceMember,
    SpaceMemberRole,
    Channel,
    ChannelMember
};
use App\ACL\ACL;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChannelRoutesTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * The space owner can create a channel
     */
    public function test_space_owner_can_create_channel()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $channel = Channel::factory()->make();

        $data = [
            'name' => $channel->name,
            'description' => $channel->description,
            'latitude' => $channel->latitude,
            'longitude' => $channel->longitude,
            'privacy' => $channel->privacy,
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.channels.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);

        // check if the channel owner was added to channel_members table
        $this->assertDatabaseHas('channel_members', [
            'user_id' => $testUser->id,
            'channel_id' => $response['data']['attributes']['id'],
            'role' => 'owner'
        ]);
    }

    /**
     * Any space member can create a channel
     */
    public function test_space_member_can_create_channel()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $user = User::factory()->hasCreatedSpaces(1)->create();
        $space = $user->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        $channel = Channel::factory()->make();

        $data = [
            'name' => $channel->name,
            'description' => $channel->description,
            'latitude' => $channel->latitude,
            'longitude' => $channel->longitude,
            'privacy' => $channel->privacy,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.channels.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);

        // check if the channel owner was added to channel_members table
        $this->assertDatabaseHas('channel_members', [
            'user_id' => $testUser->id,
            'channel_id' => $response['data']['attributes']['id'],
            'role' => 'owner'
        ]);
    }

    /**
     * non space member can't create a channel
     */
    public function test_only_space_member_can_create_channel()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasCreatedSpaces(1, [
            'privacy' => 'public',
        ])->create();
        $space = $user->createdSpaces()->first();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $channel = Channel::factory()->make();

        $data = [
            'name' => $channel->name,
            'description' => $channel->description,
            'latitude' => $channel->latitude,
            'longitude' => $channel->longitude,
            'privacy' => $channel->privacy,
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.channels.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertStatus(403);
    }

    public function test_get_channels()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $owner = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($owner);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.channels.index', [
            'space' => $space->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [[
                'attributes' => [
                    'space_id' => $channel->space_id,
                    'name' => $channel->name,
                    'description' => $channel->description,
                    'latitude' => $channel->latitude,
                    'longitude' => $channel->longitude,
                    'privacy' => $channel->privacy,
                ]
            ]]
        ]);
    }

    public function test_get_channel()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $owner = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($owner);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.channels.show', [
            'space' => $space->id,
            'channel' => $channel->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'space_id' => $channel->space_id,
                    'name' => $channel->name,
                    'description' => $channel->description,
                    'latitude' => $channel->latitude,
                    'longitude' => $channel->longitude,
                    'privacy' => $channel->privacy,
                ]
            ]
        ]);
    }

    /**
     * The channel owner can update the channel
     */
    public function test_owner_can_update_channel()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $owner = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($owner);

        Passport::actingAs($testUser);

        $updatedChannel = Channel::factory()->make();

        $data = [
            'name' => $updatedChannel->name,
            'description' => $updatedChannel->description,
            'latitude' => $updatedChannel->latitude,
            'longitude' => $updatedChannel->longitude,
            'privacy' => $updatedChannel->privacy,
        ];

        $response = $this->putJson(route('spaces.channels.update', [
            'space' => $space->id,
            'channel' => $channel->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' =>  [ 'attributes' => $data ]
        ]);
    }

    /**
     * Channel admins can update the channel
     */
    public function test_admin_can_update_channel()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($ownerRole);

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($user, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($user, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelAdmin = ChannelMember::factory()->for($testUser, 'user')->state([
            'role' => 'admin',
        ])->make();
        $channel->members()->save($channelAdmin);

        $updatedChannel = Channel::factory()->make();

        $data = [
            'name' => $updatedChannel->name,
            'description' => $updatedChannel->description,
            'latitude' => $updatedChannel->latitude,
            'longitude' => $updatedChannel->longitude,
            'privacy' => $updatedChannel->privacy,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.channels.update', [
            'space' => $space->id,
            'channel' => $channel->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * Normal members can't update the channel
     */
    public function test_member_cannot_update_channel()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($user, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($user, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelMember = ChannelMember::factory()->for($testUser, 'user')->make();
        $channel->members()->save($channelMember);

        $updatedChannel = Channel::factory()->make();

        $data = [
            'name' => $updatedChannel->name,
            'description' => $updatedChannel->description,
            'latitude' => $updatedChannel->latitude,
            'longitude' => $updatedChannel->longitude,
            'privacy' => $updatedChannel->privacy,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.channels.update', [
            'space' => $space->id,
            'channel' => $channel->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The channel owner can delete the channel.
     */
    public function test_owner_can_delete_channel()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);
        
        $owner = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($owner);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.channels.destroy', [
            'space' => $space->id,
            'channel' => $channel->id,
        ]));

        $response->assertStatus(204);
    }

    /**
     * Channel admins can't delete the channel.
     */
    public function test_admin_cannot_delete_channel()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($user, 'creator')->make();
        $space->channels()->save($channel);
        
        $channelOwner = ChannelMember::factory()->for($user, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelAdmin = ChannelMember::factory()->for($testUser, 'user')->state([
            'role' => 'admin',
        ])->make();
        $channel->members()->save($channelAdmin);

        $channel->owner_id = $user->id;
        $channel->save();

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.channels.destroy', [
            'space' => $space->id,
            'channel' => $channel->id,
        ]));

        $response->assertStatus(403);
    }
}
