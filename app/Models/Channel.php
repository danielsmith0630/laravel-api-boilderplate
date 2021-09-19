<?php

namespace App\Models;

use App\ACL\ChannelACL;

class Channel extends BaseModel
{
    use ChannelACL;
    
    public const PRIVACY_TYPES = [
        'private',
        'protected',
        'public',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'privacy',
    ];

    /**
     * Get the user that created the channel.
     *
     * @return User
     */
    public function creator() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user that owns the channel.
     *
     * @return User
     */
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * Get the space that owns the channel.
     *
     * @return Space
     */
    public function space() {
        return $this->belongsTo(Space::class);
    }

    /**
     * Get the members which this channel owns
     *
     * @return array
     */
    public function members()
    {
        return $this->hasMany(ChannelMember::class);
    }
}
