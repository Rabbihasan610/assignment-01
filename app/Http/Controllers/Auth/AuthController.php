<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle incoming login request and redirect to role-specific dashboard.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->roles()->where('name', 'admin')->exists()) {
            return redirect()->route('backend.dashboard');
        }

        if ($user->roles()->where('name', 'delivery_man')->exists()) {
            return redirect()->route('deliveryman.dashboard');
        }

        if ($user->roles()->where('name', 'customer')->exists()) {
            return redirect()->route('customer.dashboard');
        }

        return redirect('/');
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
