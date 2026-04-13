<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Mifta',
            'email' => 'miftarahayu13@gmail.com',
            'password' => Hash::make('zxcvbnm'),
        ]);

        User::create([
            'name' => 'Rahayu',
            'email' => 'miftarahayu14@gmail.com',
            'password' => Hash::make('asdfghjkl'),
        ]);

    }
}