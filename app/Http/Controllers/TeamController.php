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
            $name = preg_replace('/[^a-zA-Z\s]/', '', $request->input('name'));
            $name = ucwords(strtolower($name));

            if (strlen($name) < 2) {
                return response()->json(['errors' => ['name' => ['Invalid team name.']]], 422);
            }

            $team = Team::create([
                'name'         => $name,
                'description'  => $request->input('description') ?? '',
                'created_by'   => $request->user()?->id
            ]);
        } catch(\Throwable $e){
            Log::channel('taskforge')->info('Team creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }

        return response()->json([
            'message'  => 'Team created successfully!',
            'user'     => $request->user()
        ]);
    }
}