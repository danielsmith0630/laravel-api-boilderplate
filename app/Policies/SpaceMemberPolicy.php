<?php

namespace App\Policies;

use App\Models\{
    SpaceMember,
    SpaceMemberRole,
    User,
    Space
};
use Illuminate\Auth\Access\HandlesAuthorization;

class SpaceMemberPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any members of the specified space.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space $space
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the space member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMember  $member
     * @return mixed
     */
    public function view(User $user, SpaceMember $member)
    {
        $space = request()->route('space');
        if ($space->id != $member->space_id) {
            abort(404, 'Not Found');
        }
        return true;
    }

    /**
     * Determine whether the user can create space members.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space  $space
     * @return mixed
     */
    public function create(User $user)
    {
        $request = request();
        $space = $request->route('space');
        $user_id = $request->user_id;

        $authMember = $space->members()->where('user_id', $user->id)->first();
        // when the auth user is not a member of the space, he can join the space only when
        // the space is public.
        if (
            !$authMember &&
            $user->id == $user_id &&
            $space->privacy == 'public'
        ) {
            return true;
        }

        // When the auth user is not a member of the space or he is just a normal member, he can't add other users to the space.
        if (!$authMember || $authMember->role == 'member') {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the space member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMember  $member
     * @param  string $role The role that the updated user will have.
     * @return mixed
     */
    public function update(User $user, SpaceMember $member)
    {
        $request = request();
        $space = $request->route('space');
        
        if ($space->id != $member->space_id) {
            abort(404, 'Not Found');
        }

        return $user->id == $member->user_id;
    }

    /**
     * Determine whether the user can delete the space member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Space  $space
     * @param  \App\Models\SpaceMember  $member
     * @return mixed
     */
    public function delete(User $user, SpaceMember $member)
    {
        // Can't delete the owner of the space. The ownership should be transfered first.
        if ($member->role->role == 'owner') {
            return false;
        }

        $space = $member->space;
        if (!$space) {
            return false;
        }
        $authMember = $space->members()->where('user_id', $user->id)->first();

        // Only space members can delete a member;
        if (!$authMember) {
            return false;
        }

        if ($user->id != $member->user_id) {
            // 'normal' members can't delete other members.
            if ($authMember->role->role == 'member') {
                return false;
            }

            $authMemberRoleIndex = array_search($authMember->role->role, SpaceMemberRole::ROLES);
            $memberRoleIndex = array_search($member->role->role, SpaceMemberRole::ROLES);

            // A member can't delete other members who has the role higher than his.
            if ($authMemberRoleIndex > $memberRoleIndex) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can restore the space member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMember  $member
     * @return mixed
     */
    public function restore(User $user, SpaceMember $member)
    {
        // We will update the restore policy when we work on restore API endpoint.
        return false;
    }

    /**
     * Determine whether the user can permanently delete the space member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SpaceMember  $member
     * @return mixed
     */
    public function forceDelete(User $user, SpaceMember $member)
    {
        return false;
    }
}
