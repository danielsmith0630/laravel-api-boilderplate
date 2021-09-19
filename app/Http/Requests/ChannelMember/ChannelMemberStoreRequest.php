<?php

namespace App\Http\Requests\ChannelMember;

use App\Models\ChannelMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChannelMemberStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'role' => [
                'required',
                Rule::in(ChannelMember::ROLES),
            ]
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
            'user_id' => [
                'description' => 'The ID of the user who will be a new member of the channel.',
                'example' => 1
            ],
            'role' => [
                'description' => 'The role of the channel member.',
                'example' => 'admin'
            ]
        ];
    }
}
