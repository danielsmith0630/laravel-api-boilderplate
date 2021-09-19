<?php

namespace Tests\Feature;

use App\Models\{
    User,
    UserSetting,
    SpaceMember,
    SpaceMemberRole
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use App\ACL\ACL;

class UserSettingsRoutesTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Users can create his own application setting.
     */
    public function test_post_user_setting()
    {
        $testUser = User::factory()->create();
        Passport::actingAs(
            $testUser
        );
        $setting = UserSetting::factory()->make();

        $response = $this->postJson(route('users.settings.store', [
            'user' => $testUser->id,
            'language' => $setting->language,
            'preferred_language' => $setting->preferred_language,
            'timezone' => $setting->timezone
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'language' => $setting->language,
                    'preferred_language' => $setting->preferred_language,
                    'timezone' => $setting->timezone,
                ],
            ]
        ]);
    }

    /**
     * Users can't create other users' application settings.
     */
    public function test_cannot_create_other_user_setting()
    {
        $testUser = User::factory()->create();

        $user = User::factory()->create();

        Passport::actingAs($user);
        $setting = UserSetting::factory()->make();

        Passport::actingAs($testUser);
        $response = $this->postJson(route('users.settings.store', [
            'user' => $user->id,
            'language' => $setting->language,
            'preferred_language' => $setting->preferred_language,
            'timezone' => $setting->timezone
        ]));

        $response->assertStatus(403);
    }

    public function test_get_user_settings()
    {
        $testUser = User::factory()->create();
        Passport::actingAs(
            $testUser
        );

        $response = $this->getJson(route('users.settings.index', [
            'user' => $testUser->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => User::DEFAULT_SETTING,
            ],
        ]);
    }

    /**
     * Users can get their own application settings
     */
    public function test_get_user_setting()
    {
        $testUser = User::factory()
            ->hasSetting()
            ->create();
        Passport::actingAs(
            $testUser
        );

        $response = $this->getJson(route('users.settings.show', [
            'user' => $testUser->id,
            'setting' => $testUser->setting->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'language' => $testUser->setting->language,
                    'preferred_language' => $testUser->setting->preferred_language,
                    'timezone' => $testUser->setting->timezone,
                    'id' => $testUser->setting->id
                ],
            ]
        ]);
    }

    /**
     * Users can't get application settings of other users who are private and not related via Spaces
     */
    public function test_cannot_get_settings_of_unrelated_private_user()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $user = User::factory()->create();
        $setting = UserSetting::factory()->make();
        $user->setting()->save($setting);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.settings.show', [
            'user' => $user->id,
            'setting' => $setting->id
        ]));

        $response->assertStatus(404);
    }

    /**
     * Users can get application settings of other public users.
     */
    public function test_can_get_settings_of_other_public_user()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->create();
        $setting = UserSetting::factory()->make();
        $user->setting()->save($setting);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.settings.show', [
            'user' => $user->id,
            'setting' => $setting->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'language' => $user->setting->language,
                    'preferred_language' => $user->setting->preferred_language,
                    'timezone' => $user->setting->timezone,
                    'id' => $user->setting->id,
                ]
            ]
        ]);
    }

    /**
     * Users can get application settings of other users related via Spaces.
     */
    public function test_can_get_settings_of_related_user()
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
            ->for($user, 'user')->for($space, 'space')->owner()->make();
        $member->role()->save($memberRole);

        $setting = UserSetting::factory()->make();
        $user->setting()->save($setting);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('users.settings.show', [
            'user' => $user->id,
            'setting' => $setting->id
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $user->id,
                    'language' => $user->setting->language,
                    'preferred_language' => $user->setting->preferred_language,
                    'timezone' => $user->setting->timezone,
                    'id' => $user->setting->id,
                ]
            ]
        ]);
    }

    /**
     * Users can update their own application settings.
     */
    public function test_put_user_setting()
    {
        $testUser = User::factory()
            ->hasSetting()
            ->create();
        Passport::actingAs(
            $testUser
        );

        $setting = UserSetting::factory()->make();

        $response = $this->putJson(route('users.settings.update', [
            'user' => $testUser->id,
            'setting' => $testUser->setting->id,
            'language' => $setting->language,
            'preferred_language' => $setting->preferred_language,
            'timezone' => $setting->timezone
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'user_id' => $testUser->id,
                    'language' => $setting->language,
                    'preferred_language' => $setting->preferred_language,
                    'timezone' => $setting->timezone,
                    'id' => $testUser->setting->id
                ]
            ]
        ]);
    }

    /**
     * Users can't update other user's application settings.
     */
    public function test_cannot_update_other_user_setting()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->hasSetting()->create();
        $setting = UserSetting::factory()->make();

        Passport::actingAs($testUser);

        $response = $this->putJson(route('users.settings.update', [
            'user' => $user->id,
            'setting' => $user->setting->id,
            'language' => $setting->language,
            'preferred_language' => $setting->preferred_language,
            'timezone' => $setting->timezone
        ]));

        $response->assertStatus(403);
    }

    /**
     * Users can delete their own application settings.
     */
    public function test_delete_user_setting()
    {
        $testUser = User::factory()->hasSetting()->create();
        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('users.settings.destroy', [
            'user' => $testUser->id,
            'setting' => $testUser->setting->id
        ]));

        $response->assertStatus(204);
    }

    /**
     * Users can't delete other user's application settings.
     */
    public function test_cannot_delete_other_user_setting()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $user = User::factory()->hasPrivacySetting([
            'is_public' => 1
        ])->hasSetting()->create();

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('users.settings.destroy', [
            'user' => $user->id,
            'setting' => $user->setting->id
        ]));

        $response->assertStatus(403);
    }
}
