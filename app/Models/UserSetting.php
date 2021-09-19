<?php

namespace App\Models;

use App\ACL\UserSettingACL;

class UserSetting extends BaseModel
{
    use UserSettingACL;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'language',
        'preferred_language',
        'timezone'
    ];

    /**
     * Get the user who owns this application settings
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
