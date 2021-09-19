<?php

namespace App\Models;

class Banner extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'display_state',
        'banner_type',
        'banner_type_id',
    ];

    /**
     * Get the banner's file.
     */
    public function file()
    {
        return $this->morphOne(File::class, 'files', 'file_type', 'file_type_id', 'id');
    }

    /**
     * Get the parent model (space, user, etc.) that contains this banner.
     */
    public function container()
    {
        return $this->morphTo('banners', 'banner_type', 'banner_type_id');
    }
}
