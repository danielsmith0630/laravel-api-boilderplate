<?php

namespace App\Models;

use App\ACL\UserProfileACL;

class UserProfile extends BaseModel
{
    use UserProfileACL;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'latitude',
        'longitude',
        'address',
        'bio',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        // 'avatars.file'
        'avatars:id,display_state,avatar_type,avatar_type_id',
        'avatars.file',
        'banners:id,display_state,banner_type,banner_type_id',
        'banners.file',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user's avatars.
     */
    public function avatars()
    {
        return $this->morphMany(Avatar::class, 'avatars', 'avatar_type', 'avatar_type_id', 'id');
    }

    /**
     * Get the user's banners.
     */
    public function banners()
    {
        return $this->morphMany(Banner::class, 'banners', 'banner_type', 'banner_type_id', 'id');
    }
}
