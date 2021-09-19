<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'token' => 'required|string|exists:password_resets',
        ];
    }

    public function bodyParameters()
    {
        return [
            'password' => [
                'description' => 'New password of the user.',
                'example' => '!@aA45678'
            ],
            'token' => [
                'description' => 'The reset password token',
                'example' => '568ef052009...',
            ],
        ];
    }
}
