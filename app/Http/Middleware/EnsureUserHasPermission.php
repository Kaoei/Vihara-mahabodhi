<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    /**
     * Handle an incoming request and verify role permission.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk mengakses Sistem Manajemen Vihara.');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun pengurus Anda sedang dinonaktifkan.');
        }

        if ($permission !== null && ! $user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki izin akses untuk modul ini.');
        }

        return $next($request);
    }
}
