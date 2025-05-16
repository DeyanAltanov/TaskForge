<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function createTask(CreateTaskRequest $request)
    {
        try{
            $title = preg_replace('/[^a-zA-Z\s]/', '', $request->input('title'));
            $title = ucwords(strtolower($title));

            if (strlen($title) < 2) {
                return response()->json(['errors' => ['title' => ['Invalid task title.']]], 422);
            }

            Task::create([
                'title'       => $title,
                'description' => $request->input('description'),
                'priority'    => $request->input('priority'),
                'status'      => 'pending',
                'assigned_to' => $request->filled('assigned_to') ? $request->input('assigned_to') : null,
                'team'        => $request->input('team'),
                'created_by'  => $request->user()?->id

            ]);
        } catch(\Throwable $e){
            Log::channel('taskforge')->info('Task creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }

        return response()->json([
            'message'  => 'Task created successfully!',
            'user'     => $request->user()
        ]);
    }

    public function formData()
    {
        try {
            $teams = Team::select('id', 'name')->get();
            $users = User::select('id', 'first_name', 'last_name')->get();

            return response()->json([
                'teams' => $teams,
                'users' => $users,
            ]);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->info('formData error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }
}