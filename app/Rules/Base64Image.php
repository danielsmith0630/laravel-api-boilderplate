<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64Image implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $imagedata = base64_decode($value);
        $f = finfo_open();
        $mime_type = finfo_buffer($f, $imagedata, FILEINFO_MIME_TYPE);
        $valid_types = ['image/png', 'image/jpeg', 'image/svg+xml'];
        return in_array($mime_type, $valid_types);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.base64_image');
    }
}
