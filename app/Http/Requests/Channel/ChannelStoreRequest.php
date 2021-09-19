<?php

namespace App\Http\Requests\Channel;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChannelStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
            'latitude' => 'numeric|nullable',
            'longitude' => 'numeric|nullable',
            'privacy' => [
                'required',
                Rule::in(Channel::PRIVACY_TYPES),
            ]
        ];
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'The name of the channel.',
                'example' => 'Channel1',
            ],
            'description' => [
                'description' => 'The description of the channel.',
                'example' => 'Channel1 Description',
            ],
            'latitude' => [
                'description' => 'The latitude of the channel location.',
                'example' => 39.49583400,
            ],
            'longitude' => [
                'description' => 'The longitude of the channel location.',
                'example' => -90.45345000,
            ],
            'privacy' => [
                'desciption' => 'The privacy of the channel.',
                'example' => 'private',
            ]
        ];
    }
}
