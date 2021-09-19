<?php

namespace App\Models;

class File extends BaseModel
{
    protected $fillable = [
        'user_id',
        'space_id',
        'path',
        'url',
        'name',
        'extension',
        'size',
        'mime',
        'file_type',
        'file_type_id'
    ];

    /**
     * Get the parent model (avatar, banner, image, etc.) that contains this file.
     */
    public function container()
    {
        return $this->morphTo('files', 'file_type', 'file_type_id');
    }
}
