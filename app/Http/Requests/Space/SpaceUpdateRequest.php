<?php

namespace App\Http\Requests\Space;

use App\Models\Space;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpaceUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string|max:255',
            'bio' => 'string|nullable',
            'website' => 'url|nullable',
            'phone_number' => 'phone:AUTO,US|nullable',
            'latitude' => 'numeric|nullable',
            'longitude' => 'numeric|nullable',
            'address' => 'string|nullable',
            'privacy' => [
                Rule::in(Space::PRIVACY_TYPES),
            ],
        ];
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'The name of the space.',
                'example' => 'Space 1'
            ],
            'bio' => [
                'description' => 'The biography of the space.',
                'example' => 'Space 1 bio',
            ],
            'website' => [
                'description' => 'The website url of the space.',
                'example' => 'https://space1.com'
            ],
            'phone_number' => [
                'description' => 'The phone number of the space.',
                'example' => '2242690545'
            ],
            'latitude' => [
                'description' => 'The space location latitude',
                'example' => 39.49583400
            ],
            'longitude' => [
                'description' => 'The space location longitude',
                'example' => -90.45345000
            ],
            'address' => [
                'description' => 'The address of the space',
                'example' => 'Patterson Township, IL'
            ],
            'privacy' => [
                'description' => 'The privacy of the space',
                'example' => 'private',
            ],
        ];
    }
}
