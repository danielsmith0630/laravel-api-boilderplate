<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPrivacySetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\ACL\ACL;

class UserPrivacySettingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any user privacy setting.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        $request = request();
        $routeUser = $request->route('user');

        return in_array($routeUser->id, ACL::getVisibleUserIds());
    }

    /**
     * Determine whether the user can view the user privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPrivacySetting  $privacySetting
     * @return mixed
     */
    public function view(User $user, UserPrivacySetting $setting)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $setting->user_id) {
            abort(404, 'Not Found');
        }
        return true;
    }

    /**
     * Determine whether the user can create user privacy setting.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($user->id != $routeUser->id) {
            return false;
        }

        if (!empty($user->privacySetting->id)) {
            $profile = $user->profile;
            abort(400, __('error.user_privacy_setting_already_exists', [
                'userName'=> $profile->first_name . ' ' . $profile->last_name,
            ]));
        }

        return true;
    }

    /**
     * Determine whether the user can update the user privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPrivacySetting  $setting
     * @return mixed
     */
    public function update(User $user, UserPrivacySetting $setting)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $setting->user_id) {
            abort(404, 'Not Found');
        }
        return $user->id == $setting->user_id;
    }

    /**
     * Determine whether the user can delete the user privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPrivacySetting  $setting
     * @return mixed
     */
    public function delete(User $user, UserPrivacySetting $setting)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $setting->user_id) {
            abort(404, 'Not Found');
        }
        return $user->id == $setting->user_id;
    }

    /**
     * Determine whether the user can restore the user privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPrivacySetting  $setting
     * @return mixed
     */
    public function restore(User $user, UserPrivacySetting $setting)
    {
        // We will add authorization logic for this function
        // when we handle restore endpoint.
        return false;
    }

    /**
     * Determine whether the user can permanently delete the user privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPrivacySetting  $setting
     * @return mixed
     */
    public function forceDelete(User $user, UserPrivacySetting $setting)
    {
        return false;
    }
}
