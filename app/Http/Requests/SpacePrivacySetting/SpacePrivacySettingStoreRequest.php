<?php

namespace App\Http\Requests\SpacePrivacySetting;

use Illuminate\Foundation\Http\FormRequest;

class SpacePrivacySettingStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone_number' => 'boolean',
            'location' => 'boolean',
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
            'phone_number' => [
                'description' => 'The visibility of the space phone number. Default value: 0',
                'example' => 0,
            ],
            'location' => [
                'description' => 'The visibility of the space location. Default value: 0',
                'example' => 1
            ],
        ];
    }
}
