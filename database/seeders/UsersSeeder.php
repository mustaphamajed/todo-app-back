<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $pass = Hash::make('Todo&1234');

        User::create([
            'name' => 'user',
            'firstname' => 'user',
            'phone' => '12345678',
            'email' => 'user@gmail.tn',
            'password' => $pass,
            'email_verified_at' => now()
        ]);
    }
}
