<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTutorPortal
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('tutor.login');
        }

        $user = $request->user();

        if ($user && $user->role === 'tutor' && $user->tutor) {
            if ((bool) $user->must_change_password
                && ! $request->routeIs('tutor.change-password')
                && ! $request->routeIs('tutor.logout')) {
                return redirect()->route('tutor.change-password');
            }

            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tutor.login');
    }
}

