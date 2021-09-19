<?php

namespace App\Policies;

use App\Models\SpacePrivacySetting;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpacePrivacySettingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any space privacy settings.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // space privacy setting that the user can see will be restricted by SpacePrivacySettingScope
        return true;
    }

    /**
     * Determine whether the user can view the space privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpacePrivacySetting  $setting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, SpacePrivacySetting $setting)
    {
        return true;
    }

    /**
     * Determine whether the user can create space privacy settings.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        $request = request();
        $space = $request->route('space');
        $authMember = $space->members()->where('user_id', $user->id)->first();

        // The user should be an owner or admin to create space privacy setting
        if (!$authMember || !in_array($authMember->role->role, [ 'owner', 'admin' ])) {
            return false;
        }

        // If the privacy setting for the space is already exists, can't create another one.
        $setting = $space->privacySetting;
        return !$setting->id;
    }

    /**
     * Determine whether the user can update the space privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpacePrivacySetting  $setting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, SpacePrivacySetting $setting)
    {
        $request = request();
        $space = $request->route('space');

        if ($space->id != $setting->space_id) {
            abort(404, 'Not Found');
        }

        $authMember = $space->members()->where('user_id', $user->id)->first();

        // The user should be an owner or admin to update a space privacy setting
        return $authMember && in_array($authMember->role->role, [ 'owner', 'admin' ]);
    }

    /**
     * Determine whether the user can delete the space privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpacePrivacySetting  $setting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, SpacePrivacySetting $setting)
    {
        $request = request();
        $space = $request->route('space');

        if ($space->id != $setting->space_id) {
            abort(404, 'Not Found');
        }

        $authMember = $space->members()->where('user_id', $user->id)->first();

        // The user should be an owner or admin to delete a space privacy setting
        return $authMember && in_array($authMember->role->role, [ 'owner', 'admin' ]);
    }

    /**
     * Determine whether the user can restore the space privacy setting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpacePrivacySetting  $setting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, SpacePrivacySetting $setting)
    {
        // This will be reimplemented when we add restore endpoint.
        return false;
    }

    /**
     * Determine whether the user can permanently delete the space privacy settings.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpacePrivacySetting  $setting
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, SpacePrivacySetting $setting)
    {
        return false;
    }
}
