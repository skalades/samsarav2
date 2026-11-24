<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Superadmin: kelola akun petugas & level aksesnya.
 */
class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $level = $request->query('level');

        $users = User::orderBy('name')->get()
            ->when($level, fn ($c) => $c->filter(fn (User $u) => $u->level() === $level))
            ->values();

        $counts = User::all()->groupBy(fn (User $u) => $u->level())->map->count();

        return view('management.users.index', [
            'users' => $users,
            'level' => $level,
            'counts' => $counts,
            'roles' => User::ROLES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create($data + ['status' => 'active']);

        AuditService::log(
            action: 'STAFF_CREATED',
            entityType: 'User',
            entityId: $user->id,
            description: "Akun petugas {$user->name} ({$user->roleLabel()}) dibuat.",
        );

        return redirect()->route('management.users.index')->with('success', "Akun {$user->name} ({$user->roleLabel()}) berhasil dibuat.");
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', Password::min(8)],
        ]);

        if ($user->id === Auth::id() && $data['role'] !== User::ROLE_SUPERADMIN) {
            return back()->with('error', 'Anda tidak bisa menurunkan level akun Anda sendiri.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $oldLevel = $user->roleLabel();
        $user->update($data);

        AuditService::log(
            action: 'STAFF_UPDATED',
            entityType: 'User',
            entityId: $user->id,
            description: "Akun {$user->name} diperbarui ({$oldLevel} → {$user->roleLabel()})".(isset($data['password']) ? ', kata sandi direset.' : '.'),
        );

        return redirect()->route('management.users.index')->with('success', "Akun {$user->name} diperbarui.");
    }

    public function toggleStatus(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
        }

        $user->update(['status' => $user->isActive() ? 'suspended' : 'active']);

        AuditService::log(
            action: $user->isActive() ? 'STAFF_ACTIVATED' : 'STAFF_SUSPENDED',
            entityType: 'User',
            entityId: $user->id,
            description: "Akun {$user->name} ".($user->isActive() ? 'diaktifkan kembali.' : 'dinonaktifkan.'),
        );

        return back()->with('success', "Akun {$user->name} ".($user->isActive() ? 'aktif kembali.' : 'dinonaktifkan.'));
    }
}
