<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoLoginController extends Controller
{
    public function handle(Request $request, User $user)
    {
        if (Auth::check()) {
            if (Auth::id() === $user->id) {
                // Already logged in as this user
                return redirect()->route($user->isInvestor() ? 'dashboard' : 'console.dashboard')
                    ->with('success', 'Berhasil login otomatis.');
            } else {
                // Logged in as different user
                return view('auth.autologin-confirm', compact('user'));
            }
        }

        // Not logged in, log them in
        Auth::login($user);
        return redirect()->route($user->isInvestor() ? 'dashboard' : 'console.dashboard')
            ->with('success', 'Berhasil login otomatis.');
    }

    public function forceLogin(Request $request, User $user)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::login($user);
        return redirect()->route($user->isInvestor() ? 'dashboard' : 'console.dashboard')
            ->with('success', 'Berhasil ganti akun dan login otomatis.');
    }
}
