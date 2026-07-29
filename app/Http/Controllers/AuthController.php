<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Redirect root to login or appropriate dashboard if authenticated.
     */
    public function index()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return redirect()->route('login');
    }

    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle the authentication request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if organization is approved
            if ($user->role === 'organization' && $user->status !== 'approved') {
                Auth::logout();
                $msg = $user->status === 'pending'
                    ? 'Your organization account is undergoing compliance review.'
                    : 'Your organization account has been declined.';
                return back()->withErrors([
                    'email' => $msg,
                ])->withInput($request->only('email', 'remember'));
            }

            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Helper to redirect based on user role.
     */
    private function redirectBasedOnRole($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'organization':
                return redirect()->route('org.dashboard');
            case 'volunteer':
                return redirect()->route('volunteer.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login');
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markNotificationRead($id)
    {
        $notification = DB::table('notifications')
            ->where('notifiable_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            DB::table('notifications')
                ->where('id', $id)
                ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
