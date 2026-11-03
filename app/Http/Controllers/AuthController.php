<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')->with('success', 'Welcome to the community. Your prompt studio is ready.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('prompts.index');
    }
}
