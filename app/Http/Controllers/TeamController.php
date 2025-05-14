<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CreateTeamRequest;
use App\Models\Team;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TeamController extends Controller
{
    public function createTeam(CreateTeamRequest $request)
    {
        try{
            $team = Team::create([
                'name'         => $request->input('name'),
                'description' => $request->input('description') ?? '',
                'created_by'   => $request->user()?->id
            ]);
        } catch(\Throwable $e){
            Log::channel('taskforge')->info('Team creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }

        return response()->json([
            'message'  => 'Team created successfully!',
            'redirect' => 'dashboard',
            'user' => $request->user()
        ]);
    }
}