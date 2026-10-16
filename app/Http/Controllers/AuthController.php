<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $demoUsers = collect();
        if (app()->isLocal() || config('app.demo_mode', false)) {
            $demoUsers = User::with('branch')->whereIn('email', [
                'admin@courier.test',
                'manager.jkt@courier.test',
                'hub.cgk@courier.test',
                'courier.budi@courier.test',
                'cashier.siti@courier.test',
            ])->get();
        }

        return view('auth.login', compact('demoUsers'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Your account has been deactivated.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        if (!app()->isLocal() && !config('app.demo_mode', false)) {
            abort(403, 'Quick demo login is disabled in this environment.');
        }

        $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->user_id);

        if (!$user->is_active) {
            return redirect()->route('login')->withErrors(['email' => 'Selected account is deactivated.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', "Logged in as {$user->name} ({$user->role_label})");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been successfully logged out.');
    }
}
