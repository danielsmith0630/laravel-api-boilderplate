<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\ACL\ACL;

class UserSettingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any user settings.
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
     * Determine whether the user can view the user setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserSetting  $setting
     * @return mixed
     */
    public function view(User $user, UserSetting $setting)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $setting->user_id) {
            abort(404, 'Not Found');
        }
        return true;
    }

    /**
     * Determine whether the user can create user settings.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        $request = request();
        $routeUser = $request->route('user');
        
        return $user->id == $routeUser->id;
    }

    /**
     * Determine whether the user can update the user setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserSetting  $setting
     * @return mixed
     */
    public function update(User $user, UserSetting $setting)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $setting->user_id) {
            abort(404, 'Not Found');
        }
        return $user->id == $setting->user_id;
    }

    /**
     * Determine whether the user can delete the user setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserSetting  $setting
     * @return mixed
     */
    public function delete(User $user, UserSetting $setting)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $setting->user_id) {
            abort(404, 'Not Found');
        }
        
        return $user->id == $setting->user_id;
    }

    /**
     * Determine whether the user can restore the user setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserSetting  $userSetting
     * @return mixed
     */
    public function restore(User $user, UserSetting $userSetting)
    {
        return $user->id == $userSetting->user_id;
    }

    /**
     * Determine whether the user can permanently delete the user setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserSetting  $userSetting
     * @return mixed
     */
    public function forceDelete(User $user, UserSetting $userSetting)
    {
        return false;
    }
}
