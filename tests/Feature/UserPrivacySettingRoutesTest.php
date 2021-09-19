<?php

namespace Tests\Feature;

use App\Models\{
    User,
    UserPrivacySetting,
    SpaceMember,
    SpaceMemberRole
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use App\ACL\ACL;

class UserPrivacySettingRoutesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Users can create his own privacy setting.
     */
    public function test_post_user_privacy_setting()
    {
        $testUser = User::factory()->create();
        Passport::actingAs(
            $testUser
        );
        $setting = UserPrivacySetting::factory()
            ->publicUser()->publicMessages()->make();

        $response = $this->postJson(route('users.privacy-settings.store', [
            'user' => $testUser->id,
        ]), [
            'location' => $setting->location,
            'phone_number' => $setting->phone_number,
            'last_name' => $setting->last_name,
            'is_public' => $setting->is_public,
            'public_messages' => $setting->public_messages
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'location' => $setting->location,
                    'phone_number' => $setting->phone_number,
                    'last_name' => $setting->last_name,
                    'is_public' => $setting->is_public,
                    'public_messages' => $setting->public_messages,
                ]
            ]
        ]);
    }

    /**
     * Users can't create other users' privacy setting.
     */
    public function test_cannot_create_other_user_privacy_setting()
    {
        $testUser = User::factory()->create();

        $user = User::factory()->create();

        Passport::actingAs($user);
        $setting = UserPrivacySetting::factory()->make();

        Passport::actingAs($testUser);

        $response = $this->postJson(route('users.privacy-settings.store', [
            'user' => $user->id,
        ]), [
            'location' => $setting->location,
            'phone_number' => $setting->phone_number,
            'last_name' => $setting->last_name,
            'is_public' => $setting->is_public,
            'public_messages' => $setting->public_messages,
        ]);

        $response->assertStatus(403);
    }
    
    public function test_get_user_privacy_settings()
    {
        $testUser = User::factory()->create();
        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.privacy-settings.index', [
            'user' => $testUser->id,
        ]));
        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => User::DEFAULT_USER_PRIVACY_SETTING
            ],
        ]);
    }

    /**
     * Users can get their own privacy setting
     */
    public function test_get_user_privacy_setting()
    {
        $testUser = User::factory()
            ->hasPrivacySetting()
            ->create();
        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.privacy-settings.show', [
            'user' => $testUser->id,
            'privacy_setting' => $testUser->privacySetting->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'location' => $testUser->privacySetting->location,
                    'phone_number' => $testUser->privacySetting->phone_number,
                    'last_name' => $testUser->privacySetting->last_name,
                    'is_public' => $testUser->privacySetting->is_public,
                    'public_messages' => $testUser->privacySetting->public_messages,
                ],
            ]
        ]);
    }

    /**
     * Users can't get privacy setting of other users who are private and not related via Spaces
     */
    public function test_cannot_get_privacy_setting_of_unrelated_private_user()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $user = User::factory()->create();
        $setting = UserPrivacySetting::factory()->make();
        $user->privacySetting()->save($setting);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.privacy-settings.show', [
            'user' => $user->id,
            'privacy_setting' => $setting->id
        ]));

        $response->assertStatus(404);
    }

    /**
     * Users can get privacy setting of other public users.
     */
    public function test_can_get_privacy_setting_of_other_public_user()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1,
            'public_messages' => 1,
        ])->create();

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.privacy-settings.show', [
            'user' => $user->id,
            'privacy_setting' => $user->privacySetting->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'location' => $user->privacySetting->location,
                    'phone_number' => $user->privacySetting->phone_number,
                    'last_name' => $user->privacySetting->last_name,
                    'is_public' => $user->privacySetting->is_public,
                    'public_messages' => $user->privacySetting->public_messages
                ],
            ]
        ]);
    }

    /**
     * Users can get privacy setting of other users related via Spaces.
     */
    public function test_can_get_privacy_setting_of_related_user()
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

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.privacy-settings.show', [
            'user' => $user->id,
            'privacy_setting' => $user->privacySetting->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'location' => $user->privacySetting->location,
                    'phone_number' => $user->privacySetting->phone_number,
                    'last_name' => $user->privacySetting->last_name,
                    'is_public' => $user->privacySetting->is_public,
                    'public_messages' => $user->privacySetting->public_messages,
                ]
            ]
        ]);
    }

    /**
     * Users can update their own privacy setting.
     */
    public function test_put_user_privacy_setting()
    {
        $testUser = User::factory()
            ->hasPrivacySetting()
            ->create();
        Passport::actingAs(
            $testUser
        );
        $setting = UserPrivacySetting::factory()
            ->publicLocation()
            ->publicPhoneNumber()
            ->publicLastName()
            ->publicUser()
            ->publicMessages()
            ->make();

        $response = $this->putJson(route('users.privacy-settings.update', [
          'user' => $testUser->id,
          'privacy_setting' => $testUser->privacySetting->id
        ]), [
            'location' => $setting->location,
            'phone_number' => $setting->phone_number,
            'last_name' => $setting->last_name,
            'is_public' => $setting->is_public,
            'public_messages' => $setting->public_messages,
        ]);

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'location' => $setting->location,
                    'phone_number' => $setting->phone_number,
                    'last_name' => $setting->last_name,
                    'is_public' => $setting->is_public,
                    'public_messages' => $setting->public_messages,
                ]
            ]
        ]);
    }

    /**
     * Users can't update other user's privacy setting.
     */
    public function test_cannot_update_other_user_privacy_setting()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->create();

        $setting = UserPrivacySetting::factory()->make();

        Passport::actingAs($testUser);

        $response = $this->putJson(route('users.privacy-settings.update', [
            'user' => $user->id,
            'privacy_setting' => $user->privacySetting->id,
        ]), [
            'location' => $setting->location,
            'phone_number' => $setting->phone_number,
            'last_name' => $setting->last_name,
            'is_public' => $setting->is_public,
            'public_messages' => $setting->public_messages,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Users can delete their own privacy setting
     */
    public function test_delete_user_privacy_setting()
    {
        $testUser = User::factory()
            ->hasPrivacySetting()
            ->create();
        Passport::actingAs(
            $testUser
        );

        $response = $this->deleteJson(route('users.privacy-settings.destroy', [
            'user' => $testUser->id,
            'privacy_setting' => $testUser->privacySetting->id,
        ]));
        
        $response->assertStatus(204);
    }

    /**
     * Users can't delete other user's privacy setting.
     */
    public function test_cannot_delete_other_user_privacy_setting()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->create();

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('users.privacy-settings.destroy', [
            'user' => $user->id,
            'privacy_setting' => $user->privacySetting->id
        ]));

        $response->assertStatus(403);
    }
}
