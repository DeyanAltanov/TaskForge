<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function createTask(CreateTaskRequest $request)
    {
        try {
            $title = trim($request->input('title'));
            $title = mb_convert_case($title, MB_CASE_TITLE, "UTF-8");
    
            if (mb_strlen($title) < 2) {
                return response()->json(['errors' => ['title' => ['Invalid task title.']]], 422);
            }

            Task::create([
                'title'       => $title,
                'description' => $request->input('description'),
                'priority'    => $request->input('priority'),
                'status'      => 'pending',
                'assigned_to' => $request->filled('assigned_to') ? $request->input('assigned_to') : null,
                'team'        => (int) $request->input('team'),
                'created_by'  => $request->user()?->id,
            ]);

            return response()->json([
                'message'  => 'Task created successfully!',
                'user'     => $request->user()
            ]);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->info('Task creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function task(int $task_id)
    {
        $task = Task::select('id','title','description','status','priority','created_at','team','assigned_to')
            ->with([
                'team:id,name',
                'assigned_to:id,first_name,last_name',
                'comments' => function ($q) {
                    $q->select('id','task_id','user_id','comment','created_at')
                    ->orderBy('created_at','asc')
                    ->with('user:id,first_name,last_name');
                },
            ])
            ->findOrFail($task_id);

        return response()->json($task);
    }

    public function tasks(Request $request, $user_id = null)
    {
        try {
            $perPage = (int) $request->query('per_page', 12);
            $page    = max(1, (int) $request->query('page', 1));

            $sorts   = ['status', 'priority', 'team', 'created_at'];
            $sortBy  = $request->query('sort_by');
            $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

            $tasksQuery = Task::select(
                    'id',
                    'title',
                    'description',
                    'status',
                    'priority',
                    'assigned_to',
                    'created_at',
                    'team'
                )
                ->with([
                    'team:id,name',
                    'assigned_to:id,first_name,last_name',
                ]);

            if (!empty($user_id)) {
                $tasksQuery->where('assigned_to', $user_id);
            }

            if ($sortBy && in_array($sortBy, $sorts, true)) {
                $tasksQuery->reorder()
                           ->orderBy($sortBy, $sortDir)
                           ->orderBy('id', 'asc');
            } else {
                if (empty($user_id)) {
                    $tasksQuery->reorder()
                               ->orderByRaw('assigned_to IS NULL DESC')
                               ->orderBy('priority', 'desc')
                               ->orderBy('id', 'asc');
                } else {
                    $tasksQuery->reorder()
                               ->orderBy('priority', 'desc')
                               ->orderBy('id', 'asc');
                }
            }

            $tasks = $tasksQuery->paginate($perPage, ['*'], 'page', $page);

            return response()->json($tasks);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('allTasks error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }        
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