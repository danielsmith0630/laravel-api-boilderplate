<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\ACL\UserPrivacySettingACL;

class UserPrivacySetting extends BaseModel
{
    use UserPrivacySettingACL;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'last_name',
        'phone_number',
        'location',
        'is_public',
        'public_messages',
    ];

    protected $casts = [
        'last_name' => 'integer',
        'phone_number' => 'integer',
        'location' => 'integer',
        'is_public' => 'integer',
        'public_messages' => 'integer',
    ];

    /**
     * Get the user that owns the user privacy setting.
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
