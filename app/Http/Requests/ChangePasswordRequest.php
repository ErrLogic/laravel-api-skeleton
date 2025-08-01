<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'old_password' => ['required'],
            'new_password' => [
                'required',
                'confirmed',
                Password::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'otp_code' => 'required|numeric|digits:6',
        ];
    }
}
