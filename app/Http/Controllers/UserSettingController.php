<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use App\Models\User;
use App\Http\Requests\UserSetting\{
    UserSettingStoreRequest,
    UserSettingUpdateRequest
};
use Illuminate\Http\Request;

/**
 * @group User Setting Management
 *
 * API endpoints for user application setting management
 */
class UserSettingController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(UserSetting::class, 'setting');
    }

    /**
     * Get User Settings
     *
     * Get the application setting for a user. (In the future, it
     * will return all user application settings, but at the moment,
     * there is only one setting for a user.)
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @responseFile responses/UserSetting/setting.json
     */
    public function index(User $user)
    {
        return $this->jsonItem(
            $user->setting
        );
    }

    /**
     * Create User Setting
     *
     * Create the application setting for a user.
     *
     * @urlParam user int required The ID of the user. Example: 2
     * @responseFile responses/UserSetting/setting.json
     */
    public function store(UserSettingStoreRequest $request, User $user)
    {
        $setting = new UserSetting;

        $setting->fill($request->all());

        $user->setting()->save($setting);

        return $this->jsonItem(
            $user->setting
        );
    }

    /**
     * Get User Setting
     *
     * Get the application setting for a user
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam id int required The ID of the user application setting. Example: 1
     * @responseFile responses/UserSetting/setting.json
     */
    public function show(User $user, UserSetting $setting)
    {
      return $this->jsonItem(
          $user->setting
      );
    }

    /**
     * Update User Setting
     *
     * Update the user application setting
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam id int required The ID of the user application setting. Example: 1
     * @responseFile responses/UserSetting/setting.json
     */
    public function update(UserSettingUpdateRequest $request, User $user, UserSetting $setting)
    {
        $setting->fill($request->all());
        $setting->save();

        return $this->jsonItem(
            $user->setting
        );
    }

    /**
     * Delete User Setting
     *
     * Delete the user application setting. (mark as deleted)
     *
     * @urlParam user_id int required The ID of the user. Example: 2
     * @urlParam id int required The ID of the user application setting. Example: 1
     * @response 204
     */
    public function destroy(User $user, UserSetting $setting)
    {
        $user->setting()->delete();
        return response(null, 204);
    }
}
