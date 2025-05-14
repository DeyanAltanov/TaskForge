<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', Rule::unique('teams', 'name')],
            'description' => 'nullable'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.'
        ];
    }
}