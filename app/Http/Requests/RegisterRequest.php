<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'      => 'required|string|max:20|regex:/^[a-zA-Z]+$/u',
            'last_name'       => 'required|string|max:20|regex:/^[a-zA-Z]+$/u',
            'email'           => 'required|email|max:30|unique:users,email',
            'phone'           => 'required|regex:/^\+?[0-9]{7,15}$/',
            'gender'          => 'required|string|max:20',
            'password'        => [
                'required',
                'string',
                'min:8',
                'max:30',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
                'confirmed',
            ],
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:512',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.max'      => 'First name must not exceed 20 characters.',
            'first_name.regex'    => 'First name must contain only letters.',

            'last_name.required'  => 'Last name is required.',
            'last_name.max'       => 'Last name must not exceed 20 characters.',
            'last_name.regex'     => 'Last name must contain only letters.',

            'email.required'      => 'Email is required.',
            'email.email'         => 'Email must be a valid email address.',
            'email.max'           => 'Email must not exceed 30 characters.',
            'email.unique'        => 'This email is already taken.',

            'phone.required'      => 'Phone is required.',
            'phone.regex'         => 'Phone must be a valid phone number.',

            'gender.required'     => 'Gender is required.',
            'gender.max'          => 'Gender must not exceed 20 characters.',

            'password.required'   => 'Password is required.',
            'password.min'        => 'Password must be at least 8 characters.',
            'password.max'        => 'Password must not exceed 30 characters.',
            'password.regex'      => 'Password must include uppercase, lowercase, number, and symbol.',
            'password.confirmed'  => 'Password confirmation does not match.',

            'profile_picture.image' => 'Profile picture must be an image.',
            'profile_picture.mimes' => 'Profile picture must be a file of type: jpg, jpeg, png, gif, webp.',
            'profile_picture.max'   => 'Profile picture must not exceed 512KB.',
        ];
    }
}