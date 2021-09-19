<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Traits\MySoftDeletes;
use App\Traits\ModelMetaData;
use App\Scopes\{
    SpaceScope,
    ChannelScope,
    UserProfileScope,
    UserPrivacySettingScope
};

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, MySoftDeletes, ModelMetaData;

    const DEFAULT_PROFILE = [
        'first_name' => 'Guest',
        'last_name' => 'User',
        'phone_number' => null,
        'latitude' => null,
        'longitude' => null,
        'address' => null,
        'bio' => null,
    ];

    const DEFAULT_USER_PRIVACY_SETTING = [
        'location' => 0,
        'phone_number' => 0,
        'last_name' => 0,
        'is_public' => 0,
        'public_messages' => 0,
    ];

    const DEFAULT_SETTING = [
        'language' => 'en',
        'preferred_language' => 'en',
        'timezone' => 'America/Chicago'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'remember_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class)
            ->withoutGlobalScope(UserProfileScope::class)
            ->withDefault(self::DEFAULT_PROFILE);
    }

    /**
     * Get the user setting which belongs to this user
     *
     * @return UserSetting
     */
    public function setting()
    {
        return $this->hasOne(UserSetting::class)->withDefault(self::DEFAULT_SETTING);
    }

     /**
     * Get the user privacy setting which belongs to this user
     *
     * @return UserPrivacySetting
     */
    public function privacySetting()
    {
        return $this->hasOne(UserPrivacySetting::class)
            ->withoutGlobalScope(UserPrivacySettingScope::class)
            ->withDefault(self::DEFAULT_USER_PRIVACY_SETTING);
    }

    /**
     * Get all linked social accounts for the user
     *
     * @return array
     */
    public function linkedSocialAccounts()
    {
        return $this->hasMany(LinkedSocialAccount::class);
    }

    /**
     * Get the spaces which this user created
     *
     * @return array
     */
    public function createdSpaces()
    {
        return $this->hasMany(Space::class, 'created_by')->withoutGlobalScope(SpaceScope::class);
    }

    /**
     * Get the spaces which this user owns
     *
     * @return array
     */
    public function ownedSpaces()
    {
        return $this->hasMany(Space::class, 'owner_id')
            ->withoutGlobalScope(SpaceScope::class);
    }

    /**
     * Get the spaces to which this user belongs
     *
     * @return array
     */
    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'space_members')
            ->withoutGlobalScope(SpaceScope::class)
            ->as('member')
            ->wherePivot('deleted_at', null)
            ->withPivot('space_visibility', 'id');
    }

    /**
     * Get the spaces that will be shown in the user's public profile.
     *
     * @return array
     */
    public function publicSpaces()
    {
        return $this->belongsToMany(Space::class, 'space_members')
            ->withoutGlobalScope(SpaceScope::class)
            ->wherePivot('deleted_at', null)
            ->wherePivot('space_visibility', 1);
    }

    /**
     * Get the channels which this user created
     *
     * @return array
     */
    public function createdChannels()
    {
        return $this->hasMany(Channel::class, 'created_by')
            ->withoutGlobalScope(ChannelScope::class);
    }

    /**
     * Get the channels which this user owns
     *
     * @return array
     */
    public function ownedChannels()
    {
        return $this->hasMany(Channel::class, 'owner_id')
            ->withoutGlobalScope(ChannelScope::class);
    }

    /**
     * Get the channels to which this user belongs
     *
     * @return array
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'channel_members')
            ->withoutGlobalScope(ChannelScope::class)
            ->wherePivot('deleted_at', null);
    }
}
