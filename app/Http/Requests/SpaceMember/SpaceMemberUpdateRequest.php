<?php

namespace App\Http\Requests\SpaceMember;

use App\Models\SpaceMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpaceMemberUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'string|nullable',
            'phone_number' => 'phone:AUTO,US|nullable',
            'space_visibility' => 'boolean',
        ];
    }

    public function bodyParameters()
    {
        return [
            'title' => [
                'description' => 'The title of the member.',
                'example' => 'Lead Developer',
            ],
            'phone_number' => [
                'description' => 'The phone number of the member.',
                'example' => '+12242690553'
            ],
            'space_visibility' => [
                'description' => 'The visibility of the space in the user public profile.',
                'example' => 1
            ],
        ];
    }
}
