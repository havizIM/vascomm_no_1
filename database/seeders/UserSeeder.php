<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userSeeder = array([
            'name'     => ucwords('Super Admin'),
            'email'    => 'superadmin@vascomm.co.id',
            'password' => Hash::make('vascomm123'),
            'role'     => 'ADMIN'
        ], [
            'name'     => ucwords('User'),
            'email'    => 'user@vascomm.co.id',
            'password' => Hash::make('vascomm123'),
            'role'     => 'USER'
        ]);

        foreach($userSeeder as $key) {
            User::create($key);
        }
    }
}
