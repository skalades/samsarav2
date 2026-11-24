<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Membuat akun Super Admin awal untuk produksi.
     *
     * Kredensial dibaca dari env (SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD). Bila password
     * kosong, dibuat acak 20 karakter dan ditampilkan SEKALI di terminal. Akun yang sudah
     * ada tidak pernah ditimpa password-nya, sehingga seeder aman dijalankan ulang.
     */
    public function run(): void
    {
        $email = (string) config('samsara.superadmin.email');
        $password = (string) config('samsara.superadmin.password');
        $generated = false;

        if ($password !== '' && in_array($password, config('samsara.forbidden_passwords', []), true)) {
            throw new \RuntimeException('SUPERADMIN_PASSWORD memakai kata sandi bawaan yang dilarang. Gunakan kata sandi lain.');
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $existing->update(['role' => User::ROLE_SUPERADMIN, 'status' => 'active']);
            $this->command?->info("[OK] Super Admin {$email} sudah ada — password tidak diubah.");

            return;
        }

        if ($password === '') {
            $password = Str::password(20, symbols: false);
            $generated = true;
        }

        User::create([
            'email' => $email,
            'name' => (string) config('samsara.superadmin.name'),
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPERADMIN,
            'phone' => (string) config('samsara.superadmin.phone') ?: null,
            'status' => 'active',
        ]);

        if ($this->command) {
            $this->command->info('=====================================================');
            $this->command->info('[SUCCESS] Super Admin account created.');
            $this->command->info('Email    : '.$email);
            $this->command->info($generated
                ? 'Password : '.$password.'  (acak — catat sekarang, tidak akan ditampilkan lagi)'
                : 'Password : (dari SUPERADMIN_PASSWORD di .env)');
            $this->command->info('Segera ganti password setelah login pertama & hapus SUPERADMIN_PASSWORD dari .env.');
            $this->command->info('=====================================================');
        }
    }
}
