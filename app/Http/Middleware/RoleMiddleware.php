<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('management.login');
        }

        // Superadmin always has full access
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang peran untuk tindakan ini.',
                ], 403);
            }

            return redirect()->route('management.dashboard')
                ->with('error', 'Akses ditolak: Peran akun Anda ('.strtoupper($user->role).') tidak diizinkan membuka modul tersebut.');
        }

        return $next($request);
    }
}
