<?php

namespace Tests\Feature;

use App\Models\{
    User,
    Space,
    SpaceMember,
    SpaceMemberRole
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\ACL\ACL;

class SpaceMemberRoutesTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * The space owner can add a member to the space.
     */
    public function test_post_space_member()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);
        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->make();

        $data = [
            'user_id' => $user->id,
            'title' => $member->title,
            'phone_number' => $member->phone_number,
            'space_visibility' => $member->space_visibility,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);

        $this->assertDatabaseHas('space_member_roles', [
            'user_id' => $user->id,
            'space_id' => $space->id,
            'member_id' => $response['data']['attributes']['id'],
            'role' => 'member'
        ]);
    }

    /**
     * The space admin can add a member to the space.
     */
    public function test_post_space_member_by_admin()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();

        $ownerMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($ownerMember);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $ownerMember->role()->save($ownerRole);

        $adminMember = SpaceMember::factory()
            ->for($testUser, 'user')
            ->make();
        $space->members()->save($adminMember);
        $adminRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')
            ->for($space, 'space')
            ->state(['role' => 'admin'])
            ->make();
        $adminMember->role()->save($adminRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->make();

        $data = [
            'user_id' => $user->id,
            'title' => $member->title,
            'phone_number' => $member->phone_number,
            'space_visibility' => $member->space_visibility,
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The space moderator can add a member to the space.
     */
    public function test_post_space_member_by_moderator()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $ownerMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($ownerMember);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $ownerMember->role()->save($ownerRole);

        $moderator = SpaceMember::factory()
            ->for($testUser, 'user')
            ->make();
        $space->members()->save($moderator);

        $moderatorRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')
            ->for($space, 'space')
            ->state(['role' => 'moderator'])
            ->make();
        $moderator->role()->save($moderatorRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->make();

        $data = [
            'user_id' => $user->id,
            'title' => $member->title,
            'phone_number' => $member->phone_number,
            'space_visibility' => $member->space_visibility,
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The user can join a public space as a 'member'.
     */
    public function test_member_can_join_public_space()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()
            ->hasCreatedSpaces(1, [ 'privacy' => 'public' ])
            ->create();
        $space = $ownerUser->createdSpaces()->first();
        $ownerMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($ownerMember);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $ownerMember->role()->save($ownerRole);

        $member = SpaceMember::factory()->make();

        $data = [
            'user_id' => $testUser->id,
            'title' => $member->title,
            'phone_number' => $member->phone_number,
            'space_visibility' => $member->space_visibility,
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The user cannot join a protected space.
     */
    public function test_member_cannot_join_protected_space()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()
            ->hasCreatedSpaces(1, [ 'privacy' => 'protected' ])
            ->create();
        $space = $ownerUser->createdSpaces()->first();
        $ownerMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($ownerMember);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $ownerMember->role()->save($ownerRole);

        $member = SpaceMember::factory()->make();

        $data = [
            'user_id' => $testUser->id,
            'title' => $member->title,
            'phone_number' => $member->phone_number,
            'space_visibility' => $member->space_visibility,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertStatus(403);
    }

    public function test_get_space_members()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.members.index', [
            'space' => $space->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [[
                'attributes' => [
                    'title' => $member->title,
                    'phone_number' => $member->phone_number,
                    'space_visibility' => $member->space_visibility,
                ]
            ]]
        ]);
    }

    public function test_get_space_member()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.members.show', [
            'space' => $space->id,
            'member' => $member->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'title' => $member->title,
                    'phone_number' => $member->phone_number,
                    'space_visibility' => $member->space_visibility,
                ]
            ]
        ]);
    }

    /**
     * The owner can update his own member profile.
     */
    public function test_update_space_member_self()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $updatedMember = SpaceMember::factory()->make();

        $data = [
            'title' => $updatedMember->title,
            'phone_number' => $updatedMember->phone_number,
            'space_visibility' => $updatedMember->space_visibility,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.update', [
            'space' => $space->id,
            'member' => $member->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The owner can't update other members' profile
     */
    public function test_owner_cannot_update_other_space_member()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        $updatedMember = SpaceMember::factory()->make();

        $data = [
            'title' => $updatedMember->title,
            'role' => $updatedMember->role,
            'phone_number' => $updatedMember->phone_number,
            'space_visibility' => $updatedMember->space_visibility,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.update', [
            'space' => $space->id,
            'member' => $member->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The admin can't update other members' profile
     */
    public function test_admin_cannot_update_other_member()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $adminMember = SpaceMember::factory()
            ->for($testUser, 'user')
            ->make();
        $space->members()->save($adminMember);

        $adminRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')
            ->for($space, 'space')
            ->state(['role' => 'admin'])
            ->make();
        $adminMember->role()->save($adminRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        $updatedMember = SpaceMember::factory()->make();

        $data = [
            'title' => $updatedMember->title,
            'phone_number' => $updatedMember->phone_number,
            'space_visibility' => $updatedMember->space_visibility,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.update', [
            'space' => $space->id,
            'member' => $member->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The member can't update other member profile.
     */
    public function test_member_cannot_update_other_member()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $authMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($authMember);

        $authMemberRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->make();
        $authMember->role()->save($authMemberRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        $updatedMember = SpaceMember::factory()->make();

        $data = [
            'title' => $updatedMember->title,
            'role' => $updatedMember->role,
            'phone_number' => $updatedMember->phone_number,
            'space_visibility' => $updatedMember->space_visibility,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.update', [
            'space' => $space->id,
            'member' => $member->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The owner can delete other space members
     */
    public function test_delete_space_member()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.members.destroy', [
            'space' => $space->id,
            'member' => $member->id,
        ]));

        $response->assertStatus(204);
    }

    /**
     * The owner can't leave the space.
     */
    public function test_owner_cannot_leave_space()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.members.destroy', [
            'space' => $space->id,
            'member' => $owner->id,
        ]));

        $response->assertStatus(403);
    }

    /**
     * A member can leave the space
     */
    public function test_member_can_leave_space()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.members.destroy', [
            'space' => $space->id,
            'member' => $member->id,
        ]));

        $response->assertStatus(204);
    }

    /**
     * A member can't delete other space members
     */
    public function test_member_cannot_delete_other_member()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        $otherUser = User::factory()->create();
        $otherMember = SpaceMember::factory()->for($otherUser, 'user')->make();
        $space->members()->save($otherMember);

        $otherMemberRole = SpaceMemberRole::factory()
            ->for($otherUser, 'user')->for($space, 'space')->make();
        $otherMember->role()->save($otherMemberRole);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.members.destroy', [
            'space' => $space->id,
            'member' => $otherMember->id,
        ]));

        $response->assertStatus(403);
    }
}
