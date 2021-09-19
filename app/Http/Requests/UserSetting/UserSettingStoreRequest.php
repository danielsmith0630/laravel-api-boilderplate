<?php

namespace App\Http\Requests\UserSetting;

use Illuminate\Foundation\Http\FormRequest;

class UserSettingStoreRequest extends FormRequest
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
            'timezone' => 'timezone',
            'user_id' => 'unique:user_settings'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation() {
        $this->merge([ 'user_id' => $this->route('user')->id ]);
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
