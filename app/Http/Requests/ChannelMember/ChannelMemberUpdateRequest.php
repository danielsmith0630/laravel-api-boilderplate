<?php

namespace App\Http\Requests\ChannelMember;

use App\Models\ChannelMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChannelMemberUpdateRequest extends FormRequest
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
            'role' => [
                'description' => 'The role of the channel member.',
                'example' => 'admin'
            ]
        ];
    }
}
