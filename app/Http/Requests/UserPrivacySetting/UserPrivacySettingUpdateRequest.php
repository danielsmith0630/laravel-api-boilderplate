<?php

namespace App\Http\Requests\UserPrivacySetting;

use Illuminate\Foundation\Http\FormRequest;

class UserPrivacySettingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'location' => 'boolean',
            'phone_number' => 'boolean',
            'last_name' => 'boolean',
            'is_public' => 'boolean',
            'public_messages' => 'boolean',
        ];
    }

    public function bodyParameters()
    {
        return [
            'location' => [
                'description' => 'The visibility of the user location.',
                'example' => 1,
            ],
            'phone_number' => [
                'description' => 'The visibility of the user phone number.',
                'example' => 0,
            ],
            'last_name' => [
                'description' => 'The visibility of the user last name.',
                'example' => 1,
            ],
            'is_public' => [
                'description' => 'The visibility of the user. If the user is public, any user can see him/her. Default value: 0',
                'example' => 0,
            ],
            'public_messages' => [
                'description' => 'The possibility of public messages. If it is allowed, '
                    . 'anyone can send messages to you, even if you are not in the same space. Default value: 0',
                'example' => 0,
            ],
        ];
    }
}
