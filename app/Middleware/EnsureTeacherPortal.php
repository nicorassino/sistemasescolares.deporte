<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTeacherPortal
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('profesor.login');
        }

        $user = $request->user();

        if ($user && $user->role === 'teacher' && $user->teacher) {
            return $next($request);
        }

        // Si es admin, permitimos "ingresar como profesor" eligiendo un profesor.
        if ($user && $user->role === 'admin') {
            return redirect()->route('admin.impersonate.teacher');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('profesor.login');
    }
}

