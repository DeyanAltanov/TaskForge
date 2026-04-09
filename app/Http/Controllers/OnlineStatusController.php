<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlineStatusController extends Controller
{
    public function ping(Request $request)
    {
        try {
            $user = $request->user();

            User::where('id', $user->id)->update([
                'last_seen_at' => now(),
            ]);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('online ping error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function myTeamsOnline(Request $request)
    {
        try {
            $user = $request->user();

            $teamIds = TeamMember::where('user_id', $user->id)
                ->pluck('team_id')
                ->values();

            if ($teamIds->isEmpty()) {
                return response()->json(['online' => []]);
            }

            $userIds = TeamMember::whereIn('team_id', $teamIds)
                ->pluck('user_id')
                ->unique()
                ->values();

            $cutoff = now()->subSeconds(60);

            $online = User::query()
                ->select('id', 'first_name', 'last_name', 'profile_picture', 'last_seen_at')
                ->whereIn('id', $userIds)
                ->where('id', '!=', $user->id)
                ->whereNotNull('last_seen_at')
                ->where('last_seen_at', '>=', $cutoff)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            return response()->json(['online' => $online]);
        } catch (\Throwable $e) {
            Log::channel('taskforge')->error('myTeamsOnline error', ['e' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}