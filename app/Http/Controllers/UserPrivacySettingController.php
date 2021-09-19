<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{
    User,
    UserPrivacySetting
};
use App\Http\Requests\UserPrivacySetting\{
    UserPrivacySettingUpdateRequest,
    UserPrivacySettingStoreRequest
};

/**
 * @group User Privacy Setting Management
 *
 * API endpoints for user privacy setting management
 */
class UserPrivacySettingController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(UserPrivacySetting::class, 'privacy_setting');
    }

    /**
     * Get User Privacy Settings
     *
     * Get the privacy setting for a user. (In the future, it
     * will return all user privacy settings, but at the moment,
     * there is only one privacy setting for a user.)
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @responseFile responses/UserPrivacySetting/setting.json
     */
    public function index(User $user)
    {
        return $this->jsonItem(
            $user->privacySetting
        );
    }

    /**
     * Create User Privacy Setting
     *
     * Create the privacy setting for a user.
     *
     * @urlParam user int required The ID of the user. Example: 2
     * @responseFile responses/UserPrivacySetting/setting.json
     */
    public function store(UserPrivacySettingStoreRequest $request, User $user)
    {
        $user->privacySetting
            ->fill($request->all())
            ->save();

        return $this->jsonItem(
            $user->privacySetting
        );
    }

    /**
     * Get User Privacy Setting
     *
     * Get the privacy setting for a user
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam privacy_setting int required The ID of the user privacy setting. Example: 1
     * @responseFile responses/UserPrivacySetting/setting.json
     */
    public function show(User $user, UserPrivacySetting $privacy_setting)
    {
        return $this->jsonItem(
            $user->privacySetting
        );
    }

    /**
     * Update User Privacy Setting
     *
     * Update the user privacy setting
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam privacy_setting int required The ID of the user privacy setting. Example: 1
     * @responseFile responses/UserPrivacySetting/setting.json
     */
    public function update(UserPrivacySettingUpdateRequest $request, User $user, UserPrivacySetting $privacy_setting)
    {
        $user->privacySetting
            ->fill($request->all())
            ->save();

        return $this->jsonItem(
            $user->privacySetting
        );
    }

    /**
     * Delete User Privacy Setting
     *
     * Delete the user privacy setting. (mark as deleted)
     *
     * @urlParam user_id int required The ID of the user. Example: 6
     * @urlParam privacy_setting int required The ID of the user privacy setting. Example: 1
     * @response 204
     */
    public function destroy(User $user, UserPrivacySetting $privacy_setting)
    {
        $user->privacySetting()->delete();
        return response(null, 204);
    }
}
