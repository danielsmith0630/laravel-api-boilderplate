<?php

namespace App\Models;
use App\ACL\SpaceACL;
use App\Scopes\SpacePrivacySettingScope;

class Space extends BaseModel
{
    use SpaceACL;

    public const PRIVACY_TYPES = [
        'private',
        'protected',
        'public',
    ];

    const DEFAULT_PRIVACY_SETTING = [
        'phone_number' => 0,
        'location' => 0,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'bio',
        'website',
        'phone_number',
        'latitude',
        'longitude',
        'address',
        'privacy',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'avatars:id,display_state,avatar_type,avatar_type_id',
        'avatars.file',
        'banners:id,display_state,banner_type,banner_type_id',
        'banners.file',
    ];

    /**
     * Get the user that created the space.
     *
     * @return User
     */
    public function creator() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user that owns the space.
     *
     * @return User
     */
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * Get the space privacy setting which belongs to this space
     *
     * @return UserPrivacySetting
     */
    public function privacySetting()
    {
        return $this->hasOne(SpacePrivacySetting::class)
            ->withoutGlobalScope(SpacePrivacySettingScope::class)
            ->withDefault(self::DEFAULT_PRIVACY_SETTING);
    }

    /**
     * Get the space's avatars.
     */
    public function avatars()
    {
        return $this->morphMany(Avatar::class, 'avatars', 'avatar_type', 'avatar_type_id', 'id');
    }

    /**
     * Get the space's banners.
     */
    public function banners()
    {
        return $this->morphMany(Banner::class, 'banners', 'banner_type', 'banner_type_id', 'id');
    }

    /**
     * Get the members which this space owns
     *
     * @return array
     */
    public function members()
    {
        return $this->hasMany(SpaceMember::class);
    }

    /**
     * Get the channels which this space owns
     *
     * @return array
     */
    public function channels()
    {
        return $this->hasMany(Channel::class);
    }
}
