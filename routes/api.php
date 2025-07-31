<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Session\Middleware\StartSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::middleware(['guest'])->group(function () {
    Route::middleware([
        EnsureFrontendRequestsAreStateful::class,
        StartSession::class
    ])->post('/login', function (Request $request) {
        Log::channel('taskforge')->debug('🔥 LOGIN ROUTE hit', [
            'cookies' => $request->cookies->all(),
            'headers' => $request->headers->all(),
            'session_id' => session()->getId(),
            'session_data' => $request->hasSession() ? $request->session()->all() : 'no session',
        ]);

        return app(AuthController::class)->login($request);
    });

    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    StartSession::class,
    'auth:sanctum',
])->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();

        $path = public_path("uploads/avatars/{$user->id}/{$user->profile_picture}");
        if (!file_exists($path)) {
            $path = public_path("uploads/avatars/default.jpg");
        }

        $base64 = base64_encode(file_get_contents($path));
        $mime = mime_content_type($path);
        $profile_picture = "data:$mime;base64,$base64";

        return response()->json([
            'id'              => $user->id,
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'email'           => $user->email,
            'gender'          => $user->gender,
            'phone'           => $user->phone,
            'profile_picture' => $profile_picture,
        ]);
    });
    Route::get('/tasks/form-data', [TaskController::class, 'formData']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/create_team', [TeamController::class, 'createTeam']);
    Route::match(['get', 'post'], '/teams/{id}/edit', [TeamController::class, 'editTeam']);
    Route::match(['get', 'post'], '/teams/{id}/edit_team_members', [TeamController::class, 'editTeamMembers']);
    Route::get('/all_teams', [TeamController::class, 'allTeams']);
    Route::post('/teams/{id}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{team_id}/members/{user_id}', [TeamController::class,'removeMember']);
    Route::post('/create_task', [TaskController::class, 'createTask']);
    Route::post('/users/search', [UserController::class, 'search']);


    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    });
});

Route::get('/session-debug', function () {
    Log::channel('taskforge')->debug('🧪 Session Debug', [
        'session_id' => session()->getId(),
        'cookies' => request()->cookies->all(),
        'headers' => request()->headers->all(),
        'user' => request()->user(),
    ]);

    return response()->json(['status' => 'ok']);
});