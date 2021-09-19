<?php

namespace App\Models;

use App\ACL\SpacePrivacySettingACL;

class SpacePrivacySetting extends BaseModel
{
    use SpacePrivacySettingACL;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone_number',
        'location',
    ];

    protected $casts = [
        'phone_number' => 'integer',
        'location' => 'integer',
    ];

    /**
     * Get the space that owns the space privacy setting.
     */
    public function space() {
        return $this->belongsTo(Space::class);
    }
}
