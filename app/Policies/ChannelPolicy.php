<?php

namespace App\Policies;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChannelPolicy
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
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Channel $channel)
    {
        $request = request();
        $space = $request->route('space');
        if ($space->id != $channel->space_id) {
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

        return $space->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Channel $channel)
    {
        $request = request();
        $space = $request->route('space');

        if ($space->id != $channel->space_id) {
            abort(404, 'Not Found');
        }

        $member = $channel->members()->where('user_id', $user->id)->first();
        
        return $member && (
            $member->role == 'owner' || $member->role == 'admin'
        );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Channel $channel)
    {
        return $user->id == $channel->owner_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Channel $channel)
    {
        return $user->id == $channel->owner_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Channel  $channel
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Channel $channel)
    {
        return false;
    }
}
