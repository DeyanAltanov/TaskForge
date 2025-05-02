<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $path = storage_path("app/uploads/avatars/{$user->id}/{$user->profile_picture}");

        if (!file_exists($path)) {
            $path = public_path('app/uploads/avatars/default.jpg');
        }

        $imageData = base64_encode(file_get_contents($path));
        $mimeType = mime_content_type($path);

        return response()->json([
            'first_name' => $user->first_name,
            'avatar_base64' => "data:$mimeType;base64,$imageData",
        ]);
    }
}