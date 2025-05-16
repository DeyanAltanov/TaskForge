<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        Log::channel('taskforge')->info('🧪 Request team value:', ['team' => $this->input('team')]);
        Log::channel('taskforge')->info('🧪 Request user value:', ['assigned_to' => $this->input('assigned_to')]);
        return [
            'title'       => 'required|string',
            'description' => 'required|string',
            'priority'    => 'required|in:low,medium,high,critical',
            'team'        => 'required|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Title is required.',
            'description.required' => 'Description is required.',
            'priority.required'    => 'Please select a priority.',
            'team.required'        => 'The task must to be assigned to a team.',
        ];
    }
}