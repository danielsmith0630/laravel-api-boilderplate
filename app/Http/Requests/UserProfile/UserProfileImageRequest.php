<?php

namespace App\Http\Requests\UserProfile;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Base64Image;

class UserProfileImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'avatar' => ['required_without:banner', 'string', new Base64Image],
            'banner' => ['required_without:avatar', 'string', new Base64Image],
            'avatar_display_state' => 'string|max:255',
            'banner_display_state' => 'string|max:255',
        ];
    }

    /**
     * Get the body parameter descriptions and examples for documentation.
     *
     * @return array
     */
    public function bodyParameters()
    {
        return [
            'avatar' => [
                'description' => 'Base64 string. The avatar of the user.'
            ],
            'banner' => [
                'description' => 'Base64 string. The banner of the user.'
            ],
            'avatar_display_state' => [
                'description' => 'display_state of the newly added avatar. default value: “normal”',
                'example' => "normal"
            ],
            'banner_display_state' => [
                'description' => 'display_state of the newly added avatar. default value: “normal”',
                'example' => "normal"
            ]
        ];
    }
}
