<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (in_array($user->role, $roles)) {
            if (in_array($user->role, ['organization', 'volunteer']) && $user->status !== 'approved') {
                $msg = $user->status === 'pending'
                    ? 'Your account is undergoing compliance review.'
                    : 'Your account has been declined.';
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => $msg]);
            }
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
