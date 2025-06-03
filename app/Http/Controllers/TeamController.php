<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\TeamMember;
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

    public function editTeamMembers(Request $request, $team_id)
    {
        try {
            if ($request->isMethod('get')) {
                $team = Team::with('team_members.user')->findOrFail($team_id);

                return response()->json([
                    'team_name' => $team->name,
                    'members' => $team->team_members->map(function ($member) {
                        return [
                            'id' => $member->user->id,
                            'name' => $member->user->first_name . ' ' . $member->user->last_name,
                            'email' => $member->user->email,
                            'role' => $member->role,
                        ];
                    }),
                ]);
            }

            if ($request->isMethod('post')) {
                $query = $request->input('query');

                $teamMemberIds = TeamMember::where('team_id', $team_id)->pluck('user_id');

                $users = User::whereNotIn('id', $teamMemberIds)
                    ->where(function ($q) use ($query) {
                        $q->where('first_name', 'like', "%$query%")
                          ->orWhere('last_name', 'like', "%$query%");
                    })
                    ->limit(10)
                    ->get(['id', 'first_name', 'last_name', 'email']);

                return response()->json($users);
            }
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('Error while getting team members data: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function addMember(Request $request, $team_id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'nullable|string|max:255'
        ]);

        TeamMember::create([
            'team_id' => $team_id,
            'user_id' => $validated['user_id'],
            'role'    => $validated['role'] ?? null,
        ]);

        return response()->json(['message' => 'User added to team']);
    }

    public function removeMember($team_id, $user_id)
    {
        TeamMember::where('team_id', $team_id)
                ->where('user_id', $user_id)
                ->delete();

        return response()->json(['message' => 'User removed']);
    }
}