<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\ACL\ACL;

class UserProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any user profiles.
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
     * Determine whether the user can view the user profile.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserProfile  $userProfile
     * @return mixed
     */
    public function view(User $user, UserProfile $userProfile)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $userProfile->user_id) {
            abort(404, 'Not Found');
        }
        return true;
    }

    /**
     * Determine whether the user can create user profiles.
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
     * Determine whether the user can update the user profile.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserProfile  $userProfile
     * @return mixed
     */
    public function update(User $user, UserProfile $userProfile)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $userProfile->user_id) {
            abort(404, 'Not Found');
        }
        return $user->id == $userProfile->user_id;
    }

    /**
     * Determine whether the user can delete the user profile.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserProfile  $userProfile
     * @return mixed
     */
    public function delete(User $user, UserProfile $userProfile)
    {
        $request = request();
        $routeUser = $request->route('user');

        if ($routeUser->id != $userProfile->user_id) {
            abort(404, 'Not Found');
        }
        return $user->id == $userProfile->user_id;
    }

    /**
     * Determine whether the user can restore the user profile.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserProfile  $userProfile
     * @return mixed
     */
    public function restore(User $user, UserProfile $userProfile)
    {
        // We will add authorization logic for this function
        // when we handle restore endpoint.
        return false;
    }

    /**
     * Determine whether the user can permanently delete the user profile.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserProfile  $userProfile
     * @return mixed
     */
    public function forceDelete(User $user, UserProfile $userProfile)
    {
        return false;
    }
}
