<?php

namespace Tests\Feature;

use App\Models\{
    User,
    UserProfile,
    SpaceMember,
    SpaceMemberRole
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\ACL\ACL;
use Tests\TestData;

class UserProfileRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Users can create his own primary profile.
     */
    public function test_post_user_profile()
    {
        $testUser = User::factory()->create();
        Passport::actingAs($testUser);

        $profile = UserProfile::factory()->make();

        $response = $this->postJson(route('users.profiles.store', [
            'user' => $testUser->id,
        ]), [
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'phone_number' => $profile->phone_number,
            'latitude' => $profile->latitude,
            'longitude' => $profile->longitude,
            'address' => $profile->address,
            'bio' => $profile->bio,
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'phone_number' => $profile->phone_number,
                    'latitude' => $profile->latitude,
                    'longitude' => $profile->longitude,
                    'address' => $profile->address,
                    'bio' => $profile->bio,
                ]
            ]
        ]);
    }

    /**
     * Users can't create other users' primary profile.
     */
    public function test_cannot_create_other_user_profile()
    {
        $testUser = User::factory()->create();

        $user = User::factory()->create();

        Passport::actingAs($user);
        $profile = UserProfile::factory()->make();

        Passport::actingAs($testUser);

        $response = $this->postJson(route('users.profiles.store', [
            'user' => $user->id
        ]), [
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'phone_number' => $profile->phone_number,
            'latitude' => $profile->latitude,
            'longitude' => $profile->longitude,
            'address' => $profile->address,
            'bio' => $profile->bio,
        ]);

        $response->assertStatus(403);
    }

    public function test_get_user_profiles()
    {
        $testUser = User::factory()->create();
        Passport::actingAs(
            $testUser
        );

        $response = $this->getJson(route('users.profiles.index', [
            'user' => $testUser->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => User::DEFAULT_PROFILE
            ],
        ]);
    }

    /**
     * Users can get their own primary profile
     */
    public function test_get_user_profile()
    {
        $testUser = User::factory()
            ->hasProfile()
            ->create();
        Passport::actingAs(
            $testUser
        );

        $response = $this->getJson(route('users.profiles.show', [
            'user' => $testUser->id,
            'profile' => $testUser->profile->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'first_name' => $testUser->profile->first_name,
                    'last_name' => $testUser->profile->last_name,
                    'phone_number' => $testUser->profile->phone_number,
                    'latitude' => $testUser->profile->latitude,
                    'longitude' => $testUser->profile->longitude,
                    'address' => $testUser->profile->address,
                    'bio' => $testUser->profile->bio,
                    'id' => $testUser->profile->id,
                ]
            ]
        ]);
    }

    /**
     * Users can't get primary profile of other users who are private and not related via Spaces
     */
    public function test_cannot_get_profile_of_unrelated_private_user()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $user = User::factory()->create();
        $profile = UserProfile::factory()->make();
        $user->profile()->save($profile);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.profiles.show', [
            'user' => $user->id,
            'profile' => $profile->id
        ]));

        $response->assertStatus(404);
    }

    /**
     * Users can get primary profiles of other public users.
     */
    public function test_can_get_profiles_of_other_public_user()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->create();
        $profile = UserProfile::factory()->make();
        $user->profile()->save($profile);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.profiles.show', [
            'user' => $user->id,
            'profile' => $profile->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'first_name' => $user->profile->first_name,
                    'last_name' => $user->profile->last_name,
                    'phone_number' => $user->profile->phone_number,
                    'latitude' => $user->profile->latitude,
                    'longitude' => $user->profile->longitude,
                    'address' => $user->profile->address,
                    'bio' => $user->profile->bio,
                    'id' => $user->profile->id,
                ]
            ]
        ]);
    }

    /**
     * Users can get primary profile of other users related via Spaces.
     */
    public function test_can_get_profile_of_related_user()
    {
        ACL::reset();

        $testUser = User::factory()
            ->hasCreatedSpaces(1)
            ->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()
            ->for($testUser, 'user')->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 0
        ])->create();
        $member = SpaceMember::factory()->for($user, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()
            ->for($user, 'user')->for($space, 'space')->make();
        $member->role()->save($memberRole);

        $profile = UserProfile::factory()->make();
        $user->profile()->save($profile);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.profiles.show', [
            'user' => $user->id,
            'profile' => $profile->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'first_name' => $user->profile->first_name,
                    'last_name' => $user->profile->last_name,
                    'phone_number' => $user->profile->phone_number,
                    'latitude' => $user->profile->latitude,
                    'longitude' => $user->profile->longitude,
                    'address' => $user->profile->address,
                    'bio' => $user->profile->bio,
                    'id' => $user->profile->id
                ]
            ]
        ]);
    }

    /**
     * Users can update their own profile profile
     */
    public function test_put_user_profile()
    {
        $testUser = User::factory()
            ->hasProfile()
            ->create();
        Passport::actingAs(
            $testUser
        );

        $profile = UserProfile::factory()->make();

        $response = $this->putJson(route('users.profiles.update', [
            'user' => $testUser->id,
            'profile' => $testUser->profile->id,
        ]), [
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'phone_number' => $profile->phone_number,
            'latitude' => $profile->latitude,
            'longitude' => $profile->longitude,
            'address' => $profile->address,
            'bio' => $profile->bio,
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'phone_number' => $profile->phone_number,
                    'latitude' => $profile->latitude,
                    'longitude' => $profile->longitude,
                    'address' => $profile->address,
                    'bio' => $profile->bio,
                    'id' => $testUser->profile->id,
                ]
            ]
        ]);
    }

    /**
     * Users can't update other user's primary profile.
     */
    public function test_cannot_update_other_user_profile()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->hasProfile()->create();

        $profile = UserProfile::factory()->make();

        Passport::actingAs($testUser);

        $response = $this->putJson(route('users.profiles.update', [
            'user' => $user->id,
            'profile' => $user->profile->id,
        ]), [
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'phone_number' => $profile->phone_number,
            'latitude' => $profile->latitude,
            'longitude' => $profile->longitude,
            'address' => $profile->address,
            'bio' => $profile->bio,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Users can upload their own profile images
     */
    public function test_upload_user_profile_images()
    {
        $testUser = User::factory()
            ->hasProfile()
            ->create();
        Passport::actingAs(
            $testUser
        );

        $profile = $testUser->profile;

        $response = $this->putJson(route('users.profiles.upload-images', [
            'user' => $testUser->id,
            'profile' => $testUser->profile->id,
        ]), [
            'avatar' => TestData::$IMAGE_BASE64,
            'avatar_display_state' => 'holiday',
            'banner' => TestData::$IMAGE_BASE64,
            'banner_display_state' => 'normal',
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'id' => $testUser->profile->id,
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
            ],
        ]);
    }

    /**
     * Users can't upload other user's primary profile images.
     */
    public function test_cannot_upload_other_user_profile_images()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->hasProfile()->create();

        $profile = $user->profile();

        Passport::actingAs($testUser);

        $response = $this->putJson(route('users.profiles.update', [
            'user' => $user->id,
            'profile' => $user->profile->id,
        ]), [
            'avatar' => TestData::$IMAGE_BASE64,
            'avatar_display_state' => 'holiday',
            'banner' => TestData::$IMAGE_BASE64,
            'banner_display_state' => 'normal',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Users can delete their own primary profile
     */
    public function test_delete_user_profile()
    {
        $testUser = User::factory()
            ->hasProfile()
            ->create();
        Passport::actingAs(
            $testUser
        );

        $response = $this->deleteJson(route('users.profiles.destroy', [
            'user' => $testUser->id,
            'profile' => $testUser->profile->id
        ]));

        $response->assertStatus(204);
    }

    /**
     * Users can't delete other user's primary profile.
     */
    public function test_cannot_delete_other_user_profile()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->hasProfile()->create();

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('users.profiles.destroy', [
            'user' => $user->id,
            'profile' => $user->profile->id
        ]));

        $response->assertStatus(403);
    }
}
