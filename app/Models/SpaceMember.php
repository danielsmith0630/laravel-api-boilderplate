<?php

namespace App\Models;
use App\ACL\SpaceMemberACL;
use App\Scopes\SpaceMemberRoleScope;

class SpaceMember extends BaseModel
{
    use SpaceMemberACL;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'phone_number',
        'space_visibility',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'role',
        'user:id,email',
        'user.profile:id,user_id,first_name,last_name',
        'user.profile.avatars:id,display_state,avatar_type,avatar_type_id',
        'user.profile.avatars.file',
        'user.profile.banners:id,display_state,banner_type,banner_type_id',
        'user.profile.banners.file',
    ];

    const DEFAULT_ROLE = [
        'role' => 'member',
    ];

    /**
     * Get the user that owns this member profile.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the space that owns this member.
     */
    public function space() {
        return $this->belongsTo(Space::class);
    }

    /**
     * Get the role of the space member
     */
    public function role() {
        return $this->hasOne(SpaceMemberRole::class, 'member_id')
            ->withoutGlobalScope(SpaceMemberRoleScope::class)
            ->withDefault(self::DEFAULT_ROLE);
    }
}
