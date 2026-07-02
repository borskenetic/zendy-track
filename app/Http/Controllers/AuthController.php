<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Roles that may use the app after login (Zendy home, SSO, etc.).
     * OPAC (/opac) is public; patrons are no longer sent there on login.
     */
    private function redirectsToZendy(?string $role): bool
    {
        return in_array($role, ['admin', 'librarian', 'student', 'faculty'], true);
    }

    public function showLogin()
    {
        if (auth()->check()) {
            $role = auth()->user()->role;

            return $this->redirectsToZendy($role)
                ? redirect()->route('zendy.home')
                : redirect()->route('login')->with('error', 'Unauthorized role.');
        }
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $this->redirectsToZendy($user->role)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Unauthorized role.');
            }

            // Always land on Zendy after login so patrons are never sent to OPAC via `url.intended`.
            return redirect()->route('zendy.home');
        }
    
        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
