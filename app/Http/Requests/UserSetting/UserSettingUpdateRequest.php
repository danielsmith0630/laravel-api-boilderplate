<?php

namespace App\Http\Requests\UserSetting;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\UserSetting;

class UserSettingUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'language' => 'string|max:5',
            'preferred_language' => 'string|max:5',
            'timezone' => 'timezone'
        ];
    }

    public function bodyParameters()
    {
        return [
            'language' => [
                'description' => 'The user language. default: "en"',
                'example' => 'en'
            ],
            'preferred_language' => [
                'description' => 'The preferred language of the user. default: "en"',
                'example' => 'en',
            ],
            'timezone' => [
                'description' => 'The timezone of the user. default: "America/Chicago"',
                'example' => 'America/Chicago'
            ],
        ];
    }
}
