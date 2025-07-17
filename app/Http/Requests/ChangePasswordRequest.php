<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! Hash::check($this->input('old_password'), $this->user()->password)) {
                $validator->errors()->add('old_password', 'The old password is incorrect.');
            }
        });
    }
}
