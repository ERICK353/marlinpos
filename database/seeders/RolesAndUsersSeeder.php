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
                'name'     => 'Marlin Admin',
                'email'    => 'admin@marlin.local',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'phone'    => null,
            ],
            [
                'name'     => 'John Barber',
                'email'    => 'barber@marlin.local',    
                'password' => Hash::make('password'),
                'role'     => 'staff',
                'phone'    => '0712345678',
            ],
            [
                'name'     => 'Mary Reception',
                'email'    => 'reception@marlin.local',
                'password' => Hash::make('password'),
                'role'     => 'reception',
                'phone'    => '0723456789',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
