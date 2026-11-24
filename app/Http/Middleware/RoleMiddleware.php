<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Batasi akses berdasarkan 4 level: superadmin, kasir, gatekeeper, ranger.
     * Peran lama (admin, auditor, gate_officer, dll.) otomatis dipetakan ke level baru.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('management.login');
        }

        if (! $user->isActive()) {
            Auth::logout();

            return redirect()->route('management.login')
                ->with('error', 'Akun Anda dinonaktifkan. Hubungi superadmin basecamp.');
        }

        // Superadmin selalu punya akses penuh
        if ($user->isSuperadmin()) {
            return $next($request);
        }

        if (! empty($roles) && ! $user->hasRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang peran untuk tindakan ini.',
                ], 403);
            }

            return redirect()->route($user->homeRoute())
                ->with('error', 'Akses ditolak: level '.strtoupper($user->roleLabel()).' tidak diizinkan membuka modul tersebut.');
        }

        return $next($request);
    }
}
