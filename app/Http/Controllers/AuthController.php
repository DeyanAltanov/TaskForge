<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function getUser(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function register(RegisterRequest $request)
    {
        Log::channel('taskforge')->info('Entered register() method.', $request->only('email'));

        $validated = $request->validated();

        $user = User::create([
            'first_name' => ucfirst(strtolower($validated['first_name'])),
            'last_name' => ucfirst(strtolower($validated['last_name'])),
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'gender' => strtolower($validated['gender']),
            'password' => Hash::make($validated['password']),
        ]);

        Log::channel('taskforge')->info('✅ User created successfully!', $request->only('email'));

        return response()->json([
            'message' => 'Registered successfully',
            'redirect' => 'login',
        ]);
    }

    public function login(Request $request)
    {
        Log::channel('taskforge')->info('Entered login() method', $request->only('email'));

        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        }

        if (!Auth::attempt($credentials)) {
            Log::channel('taskforge')->warning('❌ Invalid credentials!', $credentials);

            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        $request->session()->regenerate();
        $request->session()->put('login_web', Auth::id());
        $user = Auth::user();

        Log::channel('taskforge')->info('✅ Successful login!', ['id' => $user->id, 'email' => $user->email]);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully!']);
    }
}