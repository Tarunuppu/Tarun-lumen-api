<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'tarunuppu', 
            'email' => 'tarun@gmail.com',
            'role' => 'Admin',
            'password' => Hash::make('12345678')
        ]);
    }
}