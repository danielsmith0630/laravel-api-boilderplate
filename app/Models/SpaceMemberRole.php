<?php

namespace App\Models;

use App\ACL\SpaceMemberRoleACL;
use App\Scopes\SpaceMemberScope;

class SpaceMemberRole extends BaseModel
{
    use SpaceMemberRoleACL;

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
     * Get the user that owns this space member role.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the space that owns this space member.
     */
    public function space() {
        return $this->belongsTo(Space::class);
    }

    /**
     * Get the space member that owns this role
     */
    public function member() {
        return $this->belongsTo(SpaceMember::class, 'member_id')
            ->withoutGlobalScope(SpaceMemberScope::class);
    }
}
