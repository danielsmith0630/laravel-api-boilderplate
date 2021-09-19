<?php

namespace App\Policies;

use App\Models\Space;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any spaces.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // spaces that the user can see will be restricted by SpaceScope.
        return true;
    }

    /**
     * Determine whether the user can view the space.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space  $space
     * @return mixed
     */
    public function view(User $user, Space $space)
    {
        return true;
    }

    /**
     * Determine whether the user can create spaces.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the space.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space  $space
     * @return mixed
     */
    public function update(User $user, Space $space)
    {
        $member = $space->members()->where('user_id', $user->id)->first();

        return $member && (
            $member->role->role == 'owner' || $member->role->role == 'admin'
        );
    }

    /**
     * Determine whether the user can delete the space.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space  $space
     * @return mixed
     */
    public function delete(User $user, Space $space)
    {
        return $user->id == $space->owner_id;
    }

    /**
     * Determine whether the user can restore the space.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space  $space
     * @return mixed
     */
    public function restore(User $user, Space $space)
    {
        return $user->id == $space->owner_id;
    }

    /**
     * Determine whether the user can permanently delete the space.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space  $space
     * @return mixed
     */
    public function forceDelete(User $user, Space $space)
    {
        return false;
    }
}
