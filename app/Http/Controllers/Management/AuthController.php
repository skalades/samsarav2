<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRoute());
        }

        return view('management.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            if (! $user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();

                return back()->withErrors(['email' => 'Akun ini dinonaktifkan. Hubungi superadmin basecamp.'])->onlyInput('email');
            }

            $user->update(['last_login_at' => Carbon::now()]);

            AuditService::log(
                action: 'STAFF_LOGIN',
                description: "Petugas {$user->name} ({$user->role}) berhasil masuk ke sistem.",
                payload: ['role' => $user->role]
            );

            return redirect()->intended(route($user->homeRoute()));
        }

        return back()->withErrors([
            'email' => 'Kredensial login tidak cocok dengan data petugas SAMSARA.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        if (app()->environment('production') || ! config('app.allow_quick_login', false)) {
            abort(403, 'Aksi tidak diizinkan: Fitur Quick Login dinonaktifkan demi keamanan RBAC.');
        }

        // One-click quick login for testing different roles
        $role = User::normalizeRole($request->input('role', User::ROLE_SUPERADMIN));
        $user = User::where('role', $role)->where('status', 'active')->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            $user->update(['last_login_at' => Carbon::now()]);

            AuditService::log(
                action: 'STAFF_QUICK_LOGIN',
                description: "Quick Login sebagai {$user->name} ({$user->role}).",
                payload: ['role' => $user->role]
            );

            return redirect()->route($user->homeRoute())->with('success', "Masuk sebagai {$user->name} ({$user->roleLabel()})");
        }

        return redirect()->route('management.login')->with('error', 'Role tidak ditemukan.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditService::log(
                action: 'STAFF_LOGOUT',
                description: "Petugas {$user->name} keluar dari sistem."
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('management.login');
    }
}
