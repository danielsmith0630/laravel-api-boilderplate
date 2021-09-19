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

class ChannelMemberRoutesTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * The channel owner can add other space members to the channel.
     */
    public function test_owner_can_add_other_channel_member()
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

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channelMember = ChannelMember::factory()->make();

        $data = [
            'user_id' => $user->id,
            'role' => $channelMember->role,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.channels.members.store', [
                'space' => $space->id,
                'channel' => $channel->id,
            ]),
            $data
        );

        $data['channel_id'] = $channel->id;

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The channel moderators can add other space members to the channel.
     */
    public function test_moderator_can_add_other_channel_member()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $ownerUser = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($ownerUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($ownerUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelModerator = ChannelMember::factory()->for($testUser, 'user')->state([
            'role' => 'moderator',
        ])->make();
        $channel->members()->save($channelModerator);

        $channelMember = ChannelMember::factory()->make();

        $data = [
            'user_id' => $user->id,
            'role' => $channelMember->role,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.channels.members.store', [
                'space' => $space->id,
                'channel' => $channel->id,
            ]),
            $data
        );

        $data['channel_id'] = $channel->id;

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * Space members can join a public channel.
     */
    public function test_space_member_can_join_public_channel()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $ownerUser = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($ownerUser, 'creator')->state([
            'privacy' => 'public',
        ])->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($ownerUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelMember = ChannelMember::factory()->make();

        $data = [
            'user_id' => $testUser->id,
            'role' => $channelMember->role,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.channels.members.store', [
                'space' => $space->id,
                'channel' => $channel->id,
            ]),
            $data
        );

        $data['channel_id'] = $channel->id;

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * Space members can't join a protected channel.
     */
    public function test_space_member_cannot_join_protected_channel()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $ownerUser = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($ownerUser, 'creator')->state([
            'privacy' => 'protected',
        ])->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($ownerUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelMember = ChannelMember::factory()->make();

        $data = [
            'user_id' => $testUser->id,
            'role' => $channelMember->role,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.channels.members.store', [
                'space' => $space->id,
                'channel' => $channel->id,
            ]),
            $data
        );

        $response->assertStatus(403);
    }

    public function test_get_channel_members()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($spaceOwnerRole);

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelMember = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($channelMember);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.channels.members.index', [
            'space' => $space->id,
            'channel' => $channel->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [[
                'attributes' => [
                    'user_id' => $channelMember->user_id,
                    'channel_id' => $channelMember->channel_id,
                    'role' => $channelMember->role,
                ]
            ]]
        ]);
    }

    public function test_get_channel_member()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($spaceOwnerRole);

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelMember = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($channelMember);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.channels.members.show', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $channelMember->user_id,
                    'channel_id' => $channelMember->channel_id,
                    'role' => $channelMember->role,
                ]
            ]
        ]);
    }

    /**
     * The owner can't degrade his role.
     * Ownership transfer will be handled in another endpoint.
     */
    public function test_owner_cannot_update_his_role()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($spaceOwnerRole);

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelMember = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($channelMember);

        $updatedMember = ChannelMember::factory()->make();

        $data = [
            'role' => $updatedMember->role,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.channels.members.update', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The channel owner can update other members' role.
     */
    public function test_owner_can_update_other_member()
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

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelMember = ChannelMember::factory()->for($user, 'user')->make();
        $channel->members()->save($channelMember);

        $data = [
            'role' => 'admin',
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.channels.members.update', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $channelMember->user_id,
                    'channel_id' => $channelMember->channel_id,
                    'role' => 'admin',
                ]
            ]
        ]);
    }

    /**
     * Channel admins can update other members' role except owners and 'owner' role.
     */
    public function test_admin_can_update_other_member()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $ownerUser = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($ownerUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($ownerUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelAdmin = ChannelMember::factory()->for($testUser, 'user')->state([
            'role' => 'admin',
        ])->make();
        $channel->members()->save($channelAdmin);

        $channelMember = ChannelMember::factory()->for($user, 'user')->make();
        $channel->members()->save($channelMember);

        $data = [
            'role' => 'moderator',
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.channels.members.update', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $channelMember->user_id,
                    'channel_id' => $channelMember->channel_id,
                    'role' => 'moderator',
                ]
            ]
        ]);
    }

    /**
     * Channel moderators can't update other members' role.
     */
    public function test_moderator_cannot_update_other_member()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $ownerUser = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->owner()->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($ownerUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($ownerUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelModerator = ChannelMember::factory()->for($testUser, 'user')->state([
            'role' => 'moderator',
        ])->make();
        $channel->members()->save($channelModerator);

        $channelMember = ChannelMember::factory()->for($user, 'user')->make();
        $channel->members()->save($channelMember);

        $data = [
            'role' => 'moderator',
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.channels.members.update', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * Channel members can't update his own role.
     */
    public function test_member_cannot_update_his_role()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $ownerUser = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($ownerUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($ownerUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelMember = ChannelMember::factory()->for($testUser, 'user')->make();
        $channel->members()->save($channelMember);

        $data = [
            'role' => 'moderator',
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.channels.members.update', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The channel owner can't leave the channel.
     */
    public function test_owner_cannot_leave_channel()
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

        $channelMember = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($channelMember);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.channels.members.destroy', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]));

        $response->assertStatus(403);
    }

    /**
     * The channel owner can delete other members.
     */
    public function test_owner_can_delete_other_member()
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

        $channel = Channel::factory()->for($testUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($testUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $channelMember = ChannelMember::factory()->for($user, 'user')->make();
        $channel->members()->save($channelMember);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.channels.members.destroy', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]));

        $response->assertStatus(204);
    }

    /**
     * Channel members can leave the channel.
     */
    public function test_member_can_leave_channel()
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

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.channels.members.destroy', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]));

        $response->assertStatus(204);
    }

    /**
     * Channel members can't delete other members.
     */
    public function test_member_cannot_delete_other_members()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $spaceOwner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($spaceOwner);

        $spaceOwnerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $spaceOwner->role()->save($spaceOwnerRole);

        $ownerUser = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $user = User::factory()->create();
        $spaceMember = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($spaceMember);

        $spaceMemberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $spaceMember->role()->save($spaceMemberRole);

        $channel = Channel::factory()->for($ownerUser, 'creator')->make();
        $space->channels()->save($channel);

        $channelOwner = ChannelMember::factory()->for($ownerUser, 'user')->owner()->make();
        $channel->members()->save($channelOwner);

        $authMember = ChannelMember::factory()->for($testUser, 'user')->make();
        $channel->members()->save($authMember);

        $channelMember = ChannelMember::factory()->for($user, 'user')->make();
        $channel->members()->save($channelMember);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.channels.members.destroy', [
            'space' => $space->id,
            'channel' => $channel->id,
            'member' => $channelMember->id,
        ]));

        $response->assertStatus(403);
    }
}
