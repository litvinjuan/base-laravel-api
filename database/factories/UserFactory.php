<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Walsh\Models\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => Hash::make('secret'),
        'remember_token' => Str::random(10),
    ];
});

$factory->state(User::class, 'socialite', function (Faker $faker) {
    return [
        'password' => null,
        'google_id' => 'abc123',
    ];
});
