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

class SpaceMemberRoleRoutesTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * The space owner can add a role for other members.
     */
    public function test_post_space_member_role()
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

        $data = [
            'role' => 'member',
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.roles.store', [
                'space' => $space->id,
                'member' => $member->id,
            ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'space_id' => $space->id,

                ],
            ],
        ]);
    }

    /**
     * The space admin can add a role to the space member.
     */
    public function test_admin_can_create_space_member_role()
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

        $adminMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($adminMember);

        $adminRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')
            ->for($space, 'space')
            ->state(['role' => 'admin'])
            ->make();
        $adminMember->role()->save($adminRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $data = [
            'role' => 'member',
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.roles.store', [
                'space' => $space->id,
                'member' => $member->id,
            ]),
            $data
        );

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The space moderator can't add a space member role.
     */
    public function test_moderator_cannot_create_space_member_role()
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

        $moderator = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($moderator);

        $moderatorRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')
            ->for($space, 'space')
            ->state(['role' => 'moderator'])
            ->make();
        $moderator->role()->save($moderatorRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $data = [
            'role' => 'member',
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.members.roles.store', [
                'space' => $space->id,
                'member' => $member->id,
            ]),
            $data
        );

        $response->assertStatus(403);
    }

    public function test_get_space_member_roles()
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

        $response = $this->getJson(route('spaces.members.roles.index', [
            'space' => $space->id,
            'member' => $member->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'space_id' => $space->id,
                    'member_id' => $member->id,
                    'role' => 'owner',
                ]
            ]
        ]);
    }

    public function test_get_space_member_role()
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

        $response = $this->getJson(route('spaces.members.roles.show', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $ownerRole->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'id' => $ownerRole->id,
                    'space_id' => $space->id,
                    'member_id' => $member->id,
                    'role' => 'owner',
                ]
            ]
        ]);
    }

    /**
     * The owner can't update his own role.
     */
    public function test_owner_cannot_update_his_own_role()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $data = [
            'role' => 'admin'
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.roles.update', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $ownerRole->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The owner can update other members' roles
     */
    public function test_owner_can_update_other_space_member_role()
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

        $data = [
            'role' => 'admin',
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.roles.update', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $memberRole->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The admin can update other members' role
     */
    public function test_admin_can_update_other_member_role()
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

        $adminMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($adminMember);

        $adminRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')
            ->state(['role' => 'admin'])
            ->make();
        $adminMember->role()->save($adminRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.roles.update', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $memberRole->id,
        ]), [
            'role' => 'moderator',
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => [
                'space_id' => $space->id,
                'member_id' => $member->id,
                'role' => 'moderator',
            ]]
        ]);
    }

    /**
     * The member can't update other member role.
     */
    public function test_member_cannot_update_other_member_role()
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
            ->for($user, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($memberRole);

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.roles.update', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $memberRole->id,
        ]), [
            'role' => 'moderator',
        ]);

        $response->assertStatus(403);
    }

    /**
     * The member can't update his own role.
     */
    public function test_member_cannot_update_his_role()
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

        $data = [
            'role' => 'moderator',
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.roles.update', [
            'space' => $space->id,
            'member' => $authMember->id,
            'role' => $authMemberRole->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The owner can transfer ownership to other member
     */
    public function test_owner_can_transfer_ownership()
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

        $response = $this->putJson(route('spaces.members.roles.make-owner', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $memberRole->id,
        ]), []);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => [
                'role' => 'owner'
            ]]
        ]);
    }

    /**
     * The admin can't transfer ownership to other members
     */
    public function test_admin_cannot_transfer_ownership()
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

        $adminMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($adminMember);

        $adminRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')
            ->state(['role' => 'admin'])
            ->make();
        $adminMember->role()->save($adminRole);

        $user = User::factory()->create();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.members.roles.make-owner', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $memberRole->id,
        ]), []);

        $response->assertStatus(403);
    }

    /**
     * The owner can delete other space member roles
     */
    public function test_owner_can_delete_other_space_member_role()
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

        $response = $this->deleteJson(route('spaces.members.roles.destroy', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $memberRole->id,
        ]));

        $response->assertStatus(204);
    }

    /**
     * The owner can't delete his own role.
     */
    public function test_owner_cannot_delete_his_own_role()
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

        $response = $this->deleteJson(route('spaces.members.roles.destroy', [
            'space' => $space->id,
            'member' => $owner->id,
            'role' => $ownerRole->id,
        ]));

        $response->assertStatus(403);
    }

    /**
     * A member cannot delete his own role
     */
    public function test_member_cannot_delete_his_own_role()
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

        $response = $this->deleteJson(route('spaces.members.roles.destroy', [
            'space' => $space->id,
            'member' => $member->id,
            'role' => $memberRole->id,
        ]));

        $response->assertStatus(403);
    }

    /**
     * A member can't delete other space member roles
     */
    public function test_member_cannot_delete_other_member_role()
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

        $response = $this->deleteJson(route('spaces.members.roles.destroy', [
            'space' => $space->id,
            'member' => $otherMember->id,
            'role' => $otherMemberRole->id,
        ]));

        $response->assertStatus(403);
    }
}
