<?php

namespace App\Http\Requests\SpaceMemberRole;

use App\Models\SpaceMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpaceMemberRoleStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'role' => [
                Rule::in(SpaceMemberRole::ROLES),
            ],
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
            'role' => [
                'description' => 'The role of the space member.',
                'example' => 'admin',
            ],
        ];
    }
}
