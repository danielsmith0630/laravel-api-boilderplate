<?php

namespace App\Policies;

use App\Models\SpaceMemberRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpaceMemberRolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMemberRole  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, SpaceMemberRole $role)
    {
        $request = request();
        $space = $request->route('space');
        $member = $request->route('member');

        if ($space->id != $member->space_id || $member->id != $role->member_id) {
            abort(404, 'Not Found');
        }
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        $request = request();
        $space = $request->route('space');
        $member = $request->route('member');
        $user_id = $member->user_id;
        $role = $request->role;

        if ($space->id != $member->space_id) {
            abort(404, 'Not Found');
        }

        $authMember = $space->members()->where('user_id', $user->id)->first();
        // when the auth user is not an admin nor owner of the space, he can't create a role of other members
        if (
            !$authMember || (
                $authMember->role->role != 'owner' && $authMember->role->role != 'admin'
            )
        ) {
            return false;
        }

        // The ownership transfer will be handled in another function.
        return $role != 'owner' && empty($member->role->id);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMemberRole  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, SpaceMemberRole $role)
    {
        $request = request();
        $space = $request->route('space');
        $member = $request->route('member');
        $newRole = $request->role;

        if ($space->id != $member->space_id || $member->id != $role->member_id) {
            abort(404, 'Not Found');
        }

        $authMember = $space->members()->where('user_id', $user->id)->first();

        // The auth user should be a member of the space.
        if (!$authMember || ($authMember->role->role != 'owner' && $authMember->role->role != 'admin')) {
            return false;
        }
        return $role->role != 'owner' && $newRole != 'owner';
    }

    /**
     * Determine whether the user can transfer the ownership.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMemberRole  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function transferOwnership(User $user, SpaceMemberRole $role)
    {
        $request = request();
        $space = $request->route('space');
        $member = $request->route('member');

        if ($space->id != $member->space_id || $member->id != $role->member_id) {
            abort(404, 'Not Found');
        }

        $authMember = $space->members()->where('user_id', $user->id)->first();

        // The auth user should be the owner of the space.
        if (!$authMember || $authMember->role->role != 'owner') {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMemberRole  $role
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, SpaceMemberRole $role)
    {
        $request = request();
        $space = $request->route('space');
        $member = $request->route('member');

        if ($space->id != $member->space_id || $member->id != $role->member_id) {
            abort(404, 'Not Found');
        }

        // Can't delete the owner role. The ownership should be transfered first.
        if ($role->role == 'owner') {
            return false;
        }

        $authMember = $space->members()->where('user_id', $user->id)->first();

        // Only space admin and owner can delete roles
        if (!$authMember || ($authMember->role->role != 'owner' && $authMember->role->role != 'admin')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMemberRole  $spaceMemberRole
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, SpaceMemberRole $spaceMemberRole)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMemberRole  $spaceMemberRole
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, SpaceMemberRole $spaceMemberRole)
    {
        return false;
    }
}
