<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $teamId  = $request->input('team_id');
        $isNumericId = is_numeric($query);

        if (strlen($query) < 3 && !$isNumericId) {
            return response()->json([]);
        }

        $users = User::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                  ->orWhere('last_name', 'like', "%$query%")
                  ->orWhere('email', 'like', "%$query%")
                  ->orWhere('id', 'like', "%$query%");
            })
            ->whereHas('teams')
            ->when($teamId, function ($q) use ($teamId) {
                $q->whereHas('teams', fn($t) => $t->where('teams.id', $teamId));
            })
            ->with(['teams:id,name'])
            ->limit(10)
            ->get();

        $results = $users->map(function ($user) {
            $teams = $user->teams->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values();
            return [
                'id'        => $user->id,
                'name'      => trim($user->first_name . ' ' . $user->last_name),
                'email'     => $user->email,
                'team_id'   => optional($user->teams->first())->id,
                'teams'     => $teams,
                'team_ids'  => $user->teams->pluck('id')->values(),
            ];
        });

        return response()->json($results);
    }    
}