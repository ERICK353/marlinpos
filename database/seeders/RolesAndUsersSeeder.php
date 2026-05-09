<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'            => 'System Developer',
                'email'           => 'developer@marlin.local',
                'password'        => Hash::make('password'),
                'role'            => 'admin',
                'commission_rate' => 0.00,
                'phone'           => null,
                'gender'          => 'male',
            ],
            [
                'name'            => 'Marlin Admin',
                'email'           => 'admin@marlin.local',
                'password'        => Hash::make('password'),
                'role'            => 'admin',
                'commission_rate' => 0.00,
                'phone'           => null,
                'gender'          => 'male',
            ],
            [
                'name'            => 'John Barber',
                'email'           => 'barber@marlin.local',    
                'password'        => Hash::make('password'),
                'role'            => 'staff',
                'commission_rate' => 40.00,
                'phone'           => '0712345678',
                'gender'          => 'male',
            ],
            [
                'name'            => 'Mary Reception',
                'email'           => 'reception@marlin.local',
                'password'        => Hash::make('password'),
                'role'            => 'reception',
                'commission_rate' => 0.00,
                'phone'           => '0723456789',
                'gender'          => 'female',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
