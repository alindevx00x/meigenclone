<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Those details did not match an account.']);
        }

        if (Auth::user()->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages(['email' => 'Your account has been suspended. Please contact an administrator.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.index'));
    }

    public function registerForm()
    {
        if (! SiteSetting::enabled('allow_registrations', true)) {
            return redirect()->route('login')->withErrors(['registration' => 'New registrations are currently closed.']);
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (! SiteSetting::enabled('allow_registrations', true)) {
            return redirect()->route('login')->withErrors(['registration' => 'New registrations are currently closed.']);
        }

        if ($request->filled('username')) {
            $rawUsername = ltrim(trim($request->input('username')), '@');
            $request->merge(['username' => strtolower($rawUsername)]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_\.]+$/',
                'unique:users,username',
                Rule::notIn(User::reservedUsernames()),
            ],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.regex' => 'The username may only contain letters, numbers, underscores, and dots.',
            'username.unique' => 'This creator username is already taken. Please choose another.',
            'username.not_in' => 'This username is reserved and cannot be used.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if (SiteSetting::enabled('require_email_verification')) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Email verification send failed: ' . $e->getMessage());
            }

            return redirect()->route('verification.notice')->with('success', 'Please check your inbox to verify your email address.');
        }

        return redirect()->route('dashboard.index')->with('success', 'Welcome to the community. Your profile is ready.');
    }

    public function checkUsername(Request $request): \Illuminate\Http\JsonResponse
    {
        $raw = (string) $request->query('username', '');
        $username = strtolower(trim(ltrim($raw, '@')));

        if ($username === '') {
            return response()->json([
                'available' => false,
                'status' => 'empty',
                'message' => 'Please enter a username.',
            ]);
        }

        if (strlen($username) < 3) {
            return response()->json([
                'available' => false,
                'status' => 'too_short',
                'message' => 'Username must be at least 3 characters.',
            ]);
        }

        if (strlen($username) > 30) {
            return response()->json([
                'available' => false,
                'status' => 'too_long',
                'message' => 'Username cannot exceed 30 characters.',
            ]);
        }

        if (! preg_match('/^[a-zA-Z0-9_\.]+$/', $username)) {
            return response()->json([
                'available' => false,
                'status' => 'invalid',
                'message' => 'Only letters, numbers, underscores, and dots are allowed.',
            ]);
        }

        if (in_array($username, User::reservedUsernames(), true)) {
            return response()->json([
                'available' => false,
                'status' => 'reserved',
                'message' => 'This username is reserved and cannot be used.',
            ]);
        }

        $exists = User::where('username', $username)->exists();
        if ($exists) {
            return response()->json([
                'available' => false,
                'status' => 'taken',
                'message' => "The username @{$username} is already taken.",
            ]);
        }

        return response()->json([
            'available' => true,
            'status' => 'available',
            'message' => "The username @{$username} is available!",
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('prompts.index');
    }
}
