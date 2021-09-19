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
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Tests\TestData;
use App\ACL\ACL;

class SpaceRoutesTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_post_space()
    {
        $testUser = User::factory()->create();
        Passport::actingAs(
            $testUser
        );
        $space = Space::factory()->make();

        $data = [
            'name' => $space->name,
            'bio' => $space->bio,
            'website' => $space->website,
            'phone_number' => $space->phone_number,
            'latitude' => $space->latitude,
            'longitude' => $space->longitude,
            'address' => $space->address,
            'privacy' => $space->privacy,
        ];

        $response = $this->postJson(route('spaces.store'), $data);

        $testData = array_merge($data, [
            'owner_id' => $testUser->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);

        // check if the space owner was added to space_members table
        $this->assertDatabaseHas('space_members', [
            'user_id' => $testUser->id,
            'space_id' => $response['data']['attributes']['id'],
        ]);

        $this->assertDatabaseHas('space_member_roles', [
            'user_id' => $testUser->id,
            'space_id' => $response['data']['attributes']['id'],
            'role' => 'owner'
        ]);
    }

    public function test_get_spaces()
    {
        $testUser = User::factory()->hasCreatedSpaces(1)->create();
        Passport::actingAs($testUser);

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);
        $role = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($role);

        $response = $this->getJson(route('spaces.index'));

        $response->assertOk();
        $response->assertJson([
            'data' => [[
                'attributes' => [
                    'name' => $space->name,
                    'bio' => $space->bio,
                    'website' => $space->website,
                    'phone_number' => $space->phone_number,
                    'latitude' => $space->latitude,
                    'longitude' => $space->longitude,
                    'address' => $space->address,
                    'privacy' => $space->privacy,
                ],
            ]],
        ]);
    }

    public function test_get_space()
    {
        $testUser = User::factory()->hasCreatedSpaces(1)->create();
        Passport::actingAs($testUser);
        
        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);
        $role = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($role);

        $response = $this->getJson(route('spaces.show', [
            'space' => $space->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'name' => $space->name,
                    'bio' => $space->bio,
                    'website' => $space->website,
                    'phone_number' => $space->phone_number,
                    'latitude' => $space->latitude,
                    'longitude' => $space->longitude,
                    'address' => $space->address,
                    'privacy' => $space->privacy,
                ],
            ]
        ]);
    }

    /**
     * The owner can update the space.
     */
    public function test_owner_can_update_space()
    {
        $testUser = User::factory()->hasCreatedSpaces(1)->create();
        Passport::actingAs($testUser);
        
        $spaceNew = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $spaceNew->members()->save($member);
        $role = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($spaceNew, 'space')->owner()->make();
        $member->role()->save($role);

        $space = Space::factory()->make();

        $data = [
            'name' => $space->name,
            'bio' => $space->bio,
            'website' => $space->website,
            'phone_number' => $space->phone_number,
            'latitude' => $space->latitude,
            'longitude' => $space->longitude,
            'address' => $space->address,
            'privacy' => $space->privacy,
        ];

        $response = $this->putJson(route('spaces.update', [
            'space' => $spaceNew->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * A admin can update the space.
     */
    public function test_admin_can_update_space()
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
            ->state([ 'role' => 'admin' ])->make();
        $adminMember->role()->save($adminRole);

        $updatedSpace = Space::factory()->make();

        $data = [
            'name' => $updatedSpace->name,
            'bio' => $updatedSpace->bio,
            'website' => $updatedSpace->website,
            'phone_number' => $updatedSpace->phone_number,
            'latitude' => $updatedSpace->latitude,
            'longitude' => $updatedSpace->longitude,
            'address' => $updatedSpace->address,
            'privacy' => $updatedSpace->privacy,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.update', [
            'space' => $space->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * A member can't update the space.
     */
    public function test_member_cannot_update_space()
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
        $memberRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->make();
        $authMember->role()->save($memberRole);

        $updatedSpace = Space::factory()->make();

        $data = [
            'name' => $updatedSpace->name,
            'bio' => $updatedSpace->bio,
            'website' => $updatedSpace->website,
            'phone_number' => $updatedSpace->phone_number,
            'latitude' => $updatedSpace->latitude,
            'longitude' => $updatedSpace->longitude,
            'address' => $updatedSpace->address,
            'privacy' => $updatedSpace->privacy,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.update', [
            'space' => $space->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The owner can upload the space avatar and banner.
     */
    public function test_owner_can_upload_space_images()
    {
        $testUser = User::factory()->hasCreatedSpaces(1)->create();
        Passport::actingAs($testUser);
        
        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);
        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $data = [
            'avatar' => TestData::$IMAGE_BASE64,
            'avatar_display_state' => 'holiday',
            'banner' => TestData::$IMAGE_BASE64,
            'banner_display_state' => 'normal',
        ];

        $response = $this->putJson(route('spaces.upload-images', [
            'space' => $space->id,
        ]), $data);

        $testData = [
            'attributes' => [
                'id' => $space->id,
                'name' => $space->name,
            ],
            'relationships' => [
                'avatars' => [
                    'data' => [
                        [
                            'type' => 'avatar',
                            'id' => 1
                        ]
                    ]
                ],
                'banners' => [
                    'data' => [
                        [
                            'type' => 'banner',
                            'id' => 1,
                        ]
                    ]
                ]
            ],
            'includes' => [
                [
                    'type' => 'avatar',
                    'attributes' => [ 'display_state' => 'holiday'],
                    'includes' => [
                        [
                            'type' => 'file',
                            'attributes' => [ 'file_type' => 'avatar' ]
                        ]
                    ]
                ],
                [
                    'type' => 'banner',
                    'attributes' => [ 'display_state' => 'normal'],
                    'includes' => [
                        [
                            'type' => 'file',
                            'attributes' => [ 'file_type' => 'banner' ]
                        ]
                    ]
                ]
            ]
        ];

        $response->assertOk();
        $response->assertJson([
            'data' => $testData
        ]);
    }

    /**
     * A member can't upload the space avatar and banner.
     */
    public function test_member_cannot_upload_space_images()
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
        $memberRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->make();
        $authMember->role()->save($memberRole);

        $data = [
            'avatar' => TestData::$IMAGE_BASE64,
            'avatar_display_state' => 'holiday',
            'banner' => TestData::$IMAGE_BASE64,
            'banner_display_state' => 'normal',
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.upload-images', [
            'space' => $space->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The owner can delete the space
     */
    public function test_owner_can_delete_space()
    {
        $testUser = User::factory()->hasCreatedSpaces(1)->create();
        Passport::actingAs($testUser);
        
        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);
        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $response = $this->deleteJson(route('spaces.destroy', [
            'space' => $space->id
        ]));

        $response->assertStatus(204);
    }

    /**
     * A admin can't delete the space
     */
    public function test_admin_cannot_delete_space()
    {
        ACL::reset();
        $testUser = User::factory()->create();
        
        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $space->owner_id = $ownerUser->id;
        $space->save();
        
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);
        $ownerRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $authMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($authMember);
        $adminRole = SpaceMemberRole::factory()
            ->for($ownerUser, 'user')
            ->for($space, 'space')
            ->state(['role' => 'admin'])
            ->make();
        $authMember->role()->save($adminRole);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.destroy', [
            'space' => $space->id
        ]));

        $response->assertStatus(403);
    }
}
