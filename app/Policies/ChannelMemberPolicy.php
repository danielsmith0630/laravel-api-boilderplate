<?php

namespace App\Policies;

use App\Models\ChannelMember;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChannelMemberPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any channel members.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        $request = request();
        $space = $request->route('space');
        $channel = $request->route('channel');

        if ($space->id != $channel->space_id) {
            abort(404, 'Not Found');
        }

        return true;
    }

    /**
     * Determine whether the user can view the channel member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChannelMember  $member
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ChannelMember $member)
    {
        $request = request();
        $space = $request->route('space');
        $channel = $request->route('channel');

        if (
            $space->id != $channel->space_id ||
            $channel->id != $member->channel_id
        ) {
            abort(404, 'Not Found');
        }

        return true;
    }

    /**
     * Determine whether the user can create channel members.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        $request = request();
        $space = $request->route('space');
        $channel = $request->route('channel');
        $user_id = $request->user_id;
        $role = $request->role;

        if ($space->id != $channel->space_id) {
            abort(404, 'Not Found');
        }

        // The user to be added to the channel should be a member of the space.
        if (!$space->members()->where('user_id', $user_id)->exists()) {
            return false;
        }
        
        $authMember = $channel->members()->where('user_id', $user->id)->first();
        // when the auth user is not a member of the channel, he can join the channel only when
        // the channel is public. And the role should be 'member'.
        if (
            !$authMember &&
            $user->id == $user_id &&
            $role == 'member' &&
            $channel->privacy == 'public'
        ) {
            return true;
        }

        // When the auth user is not a member of the channel or he is just a normal member, he can't add other users to the channel.
        if (!$authMember || $authMember->role == 'member') {
            return false;
        }

        $authMemberRoleIndex = array_search($authMember->role, ChannelMember::ROLES);
        $roleIndex = array_search($role, ChannelMember::ROLES);

        // The auth user can't add a member with a higher role than his.
        // The ownership transfer will be handled in another function.
        return $authMemberRoleIndex <= $roleIndex && $role != 'owner';
    }

    /**
     * Determine whether the user can update the channel member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChannelMember  $member
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ChannelMember $member)
    {
        $request = request();
        $space = $request->route('space');
        $channel = $request->route('channel');
        $role = $request->role;

        if ($space->id != $channel->space_id || $channel->id != $member->channel_id) {
            abort(404, 'Not Found');
        }

        $authMember = $channel->members()->where('user_id', $user->id)->first();

        // The auth user should be a member of the channel.
        if (!$authMember) {
            return false;
        }

        $authMemberRoleIndex = array_search($authMember->role, ChannelMember::ROLES);
        $roleIndex = array_search($role, ChannelMember::ROLES);
        $memberRoleIndex = array_search($member->role, ChannelMember::ROLES);

        // A member can't update the member role to the one higher than his own role.
        // A member can't update the member who has the higher role.
        if ($authMemberRoleIndex > $roleIndex || $authMemberRoleIndex > $memberRoleIndex) {
            return false;
        }

        // Ownership transfer authorization will be handled in another function
        if ($user->id == $member->user_id) {
            if ($authMember->role == "owner" && $role != "owner") {
                return false;
            }
        } else {
            if ($role == "owner") {
                return false;
            }
            // Only the owner and amdins can update other users' member profile.
            if ($authMember->role != 'owner' && $authMember->role != 'admin') {
                return false;
            }
        }
        return true;
    }

    /**
     * Determine whether the user can delete the channel member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChannelMember  $member
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ChannelMember $member)
    {
        $request = request();
        $space = $request->route('space');
        $channel = $request->route('channel');

        if ($space->id != $channel->space_id || $channel->id != $member->channel_id) {
            abort(404, 'Not Found');
        }

        // Can't delete the owner of the channel. The ownership should be transfered first.
        if ($member->role == 'owner') {
            return false;
        }

        $authMember = $channel->members()->where('user_id', $user->id)->first();

        // Only space members can delete a member;
        if (!$authMember) {
            return false;
        }

        if ($user->id != $member->user_id) {
            // 'normal' members can't delete other members.
            if ($authMember->role == 'member') {
                return false;
            }

            $authMemberRoleIndex = array_search($authMember->role, ChannelMember::ROLES);
            $memberRoleIndex = array_search($member->role, ChannelMember::ROLES);

            // A member can't delete other members who has the role higher than his.
            if ($authMemberRoleIndex > $memberRoleIndex) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can restore the channel member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChannelMember  $member
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ChannelMember $member)
    {
        // We will update the restore policy when we work on restore API endpoint.
        return false;
    }

    /**
     * Determine whether the user can permanently delete the channel member.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChannelMember  $channelMember
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ChannelMember $channelMember)
    {
        return false;
    }
}
