<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        $users = User::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                  ->orWhere('last_name', 'like', "%$query%")
                  ->orWhere('email', 'like', "%$query%");
            })
            ->whereHas('teams')
            ->with('teams')
            ->limit(10)
            ->get();

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'team_id' => optional($user->teams->first())->id,
            ];
        });

        return response()->json($results);
    }    
}