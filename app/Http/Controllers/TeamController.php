<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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

            Team::create([
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

    public function allTeams(){
        return response()->json([
            'teams' => Team::select('id', 'name')->get(),
            'users' => User::select('id', 'first_name', 'last_name')->get()
        ]);
    }

    public function editTeam(Request $request, $team_id){
        $team = Team::find($team_id);

        if ($request->isMethod('post')) {
            $team = Team::findOrFail($team_id);
    
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'description' => 'nullable|string|max:500'
            ]);

            $validated['updated_by'] = $request->user()?->id;

            $team->update($validated);
    
            return response()->json(['message' => 'Team updated', 'team' => $team]);
        }

        if (!$team) {
            return response()->json(['message' => 'Team not found.'], 404);
        }

        return response()->json($team);
    }
}