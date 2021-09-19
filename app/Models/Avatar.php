<?php

namespace App\Models;

class Avatar extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'display_state',
        'avatar_type',
        'avatar_type_id',
    ];

    /**
     * Get the avatar's file.
     */
    public function file()
    {
        return $this->morphOne(File::class, 'files', 'file_type', 'file_type_id', 'id');
    }

    /**
     * Get the parent model (space, user, etc.) that contains this avatar.
     */
    public function container()
    {
        return $this->morphTo('avatars', 'avatar_type', 'avatar_type_id');
    }
}
