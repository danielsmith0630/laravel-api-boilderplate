<?php

namespace Tests\Feature;

use App\Models\{
    User,
    Space,
    SpaceMember,
    SpaceMemberRole,
    SpacePrivacySetting
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\ACL\ACL;

class SpacePrivacySettingRoutesTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * The space owner can add privacy setting to the space.
     */
    public function test_post_space_privacy_setting()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $setting = SpacePrivacySetting::factory()->make();

        $data = [
            'phone_number' => $setting->phone_number,
            'location' => $setting->location,
        ];

        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.privacy-settings.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The space admin can add privacy setting to the space.
     */
    public function test_post_space_privacy_setting_by_admin()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $ownerMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($ownerMember);

        $ownerRole = SpaceMemberRole::factory()->for($ownerUser, 'user')
            ->for($space, 'space')->owner()->make();
        $ownerMember->role()->save($ownerRole);

        $adminMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($adminMember);

        $adminRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->state(['role' => 'admin'])->make();
        $adminMember->role()->save($adminRole);

        $setting = SpacePrivacySetting::factory()->make();

        $data = [
            'phone_number' => $setting->phone_number,
            'location' => $setting->location,
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.privacy-settings.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The space moderator can't add privacy setting to the space.
     */
    public function test_moderator_cannot_add_space_privacy_setting()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $ownerMember = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($ownerMember);

        $ownerRole = SpaceMemberRole::factory()->for($ownerUser, 'user')
            ->for($space, 'space')->owner()->make();
        $ownerMember->role()->save($ownerRole);

        $moderator = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($moderator);

        $moderatorRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->state(['role' => 'moderator'])->make();
        $moderator->role()->save($moderatorRole);

        $setting = SpacePrivacySetting::factory()->make();

        $data = [
            'phone_number' => $setting->phone_number,
            'location' => $setting->location,
        ];
        
        Passport::actingAs($testUser);

        $response = $this->postJson(
            route('spaces.privacy-settings.store', [ 'space' => $space->id ]),
            $data
        );

        $response->assertStatus(403);
    }

    public function test_get_space_privacy_settings()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.privacy-settings.index', [
            'space' => $space->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => Space::DEFAULT_PRIVACY_SETTING,
            ]
        ]);
    }

    public function test_get_space_privacy_setting()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $ownerRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->owner()->make();
        $member->role()->save($ownerRole);

        $setting = SpacePrivacySetting::factory()->make();
        $space->privacySetting()->save($setting);

        Passport::actingAs($testUser);

        $response = $this->getJson(route('spaces.privacy-settings.show', [
            'space' => $space->id,
            'privacy_setting' => $setting->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'attributes' => [
                    'space_id' => $space->id,
                    'phone_number' => $setting->phone_number,
                    'location' => $setting->location,
                ]
            ]
        ]);
    }

    /**
     * The owner can update space privacy setting
     */
    public function test_update_space_privacy_setting()
    {
        ACL::reset();

        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $setting = SpacePrivacySetting::factory()->make();
        $space->privacySetting()->save($setting);

        $updatedSetting = SpacePrivacySetting::factory()->make();

        $data = [
            'phone_number' => $updatedSetting->phone_number,
            'location' => $updatedSetting->location,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.privacy-settings.update', [
            'space' => $space->id,
            'privacy_setting' => $setting->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The admin can update space privacy setting.
     */
    public function test_admin_can_update_space_privacy_setting()
    {
        ACL::reset();

        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()->for($ownerUser, 'user')
            ->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $adminMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($adminMember);

        $adminRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->state(['role' => 'admin'])->make();
        $adminMember->role()->save($adminRole);

        $setting = SpacePrivacySetting::factory()->make();
        $space->privacySetting()->save($setting);

        $updatedSetting = SpacePrivacySetting::factory()->make();

        $data = [
            'phone_number' => $updatedSetting->phone_number,
            'location' => $updatedSetting->location,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.privacy-settings.update', [
            'space' => $space->id,
            'privacy_setting' => $setting->id,
        ]), $data);

        $response->assertOk();
        $response->assertJson([
            'data' => [ 'attributes' => $data ]
        ]);
    }

    /**
     * The member can't update space privacy setting.
     */
    public function test_member_cannot_update_space_privacy_setting()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()->for($ownerUser, 'user')
            ->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $authMember = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($authMember);

        $memberRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->make();
        $authMember->role()->save($memberRole);

        $setting = SpacePrivacySetting::factory()->make();
        $space->privacySetting()->save($setting);

        $updatedSetting = SpacePrivacySetting::factory()->make();

        $data = [
            'phone_number' => $updatedSetting->phone_number,
            'location' => $updatedSetting->location,
        ];

        Passport::actingAs($testUser);

        $response = $this->putJson(route('spaces.privacy-settings.update', [
            'space' => $space->id,
            'privacy_setting' => $setting->id,
        ]), $data);

        $response->assertStatus(403);
    }

    /**
     * The owner can delete space privacy setting
     */
    public function test_delete_space_privacy_setting()
    {
        ACL::reset();
        $testUser = User::factory()->hasCreatedSpaces(1)->create();

        $space = $testUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $setting = SpacePrivacySetting::factory()->make();
        $space->privacySetting()->save($setting);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.privacy-settings.destroy', [
            'space' => $space->id,
            'privacy_setting' => $setting->id,
        ]));

        $response->assertStatus(204);
    }

    /**
     * A member can't delete space privacy setting
     */
    public function test_member_cannot_delete_space_privacy_setting()
    {
        ACL::reset();
        $testUser = User::factory()->create();

        $ownerUser = User::factory()->hasCreatedSpaces(1)->create();
        $space = $ownerUser->createdSpaces()->first();
        $owner = SpaceMember::factory()->for($ownerUser, 'user')->make();
        $space->members()->save($owner);

        $ownerRole = SpaceMemberRole::factory()->for($ownerUser, 'user')
            ->for($space, 'space')->owner()->make();
        $owner->role()->save($ownerRole);

        $member = SpaceMember::factory()->for($testUser, 'user')->make();
        $space->members()->save($member);

        $memberRole = SpaceMemberRole::factory()->for($testUser, 'user')
            ->for($space, 'space')->make();
        $member->role()->save($memberRole);

        $setting = SpacePrivacySetting::factory()->make();
        $space->privacySetting()->save($setting);

        Passport::actingAs($testUser);

        $response = $this->deleteJson(route('spaces.privacy-settings.destroy', [
            'space' => $space->id,
            'privacy_setting' => $setting->id,
        ]));

        $response->assertStatus(403);
    }
}
