<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        factory(\Walsh\Models\User::class)->create([
            'email' => 'litvinjuan@gmail.com',
            'email_verified_at' => now(),
        ]);
    }
}
