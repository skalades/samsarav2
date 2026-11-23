<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates or updates only the initial Super Admin account for production.
     */
    public function run(): void
    {
        $superadmin = User::updateOrCreate(
            ['email' => 'admin@samsara.id'],
            [
                'name' => 'Super Admin SAMSARA',
                'password' => Hash::make('samsara2026'),
                'role' => 'superadmin',
                'phone' => '081211112222',
                'status' => 'active',
            ]
        );

        if ($this->command) {
            $this->command->info('=====================================================');
            $this->command->info('[SUCCESS] Super Admin account ready.');
            $this->command->info('Email    : ' . $superadmin->email);
            $this->command->info('Password : samsara2026');
            $this->command->info('Role     : ' . $superadmin->role);
            $this->command->info('=====================================================');
        }
    }
}
