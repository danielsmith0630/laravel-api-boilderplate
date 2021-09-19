<?php

namespace App\Models;

use App\ACL\ChannelMemberACL;

class ChannelMember extends BaseModel
{
    use ChannelMemberACL;

    const ROLES = [
        'owner',
        'admin',
        'moderator',
        'member',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'user:id,email',
        'user.profile:id,user_id,first_name,last_name',
        'user.profile.avatars:id,display_state,avatar_type,avatar_type_id',
        'user.profile.avatars.file',
        'user.profile.banners:id,display_state,banner_type,banner_type_id',
        'user.profile.banners.file',
    ];

    /**
     * Get the user that owns this member profile.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the channel that owns this member.
     */
    public function channel() {
        return $this->belongsTo(Channel::class);
    }
}
