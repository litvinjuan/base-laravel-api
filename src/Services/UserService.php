<?php

namespace Walsh\Services;

use Illuminate\Support\Facades\Hash;
use Walsh\Models\User;
use Walsh\Payloads\User\ChangePasswordPayload;
use Walsh\Payloads\User\CreateUserPayload;
use Walsh\Payloads\User\UpdateUserPayload;

class UserService
{
    public function create(CreateUserPayload $payload): User
    {
        /** @var User $user */
        $user = User::query()->create([
            'email' => $payload->email(),
            'password' => Hash::make($payload->password()),
            'google_id' => $payload->googleId(),
        ]);

        return $user;
    }

    public function update(User $user, UpdateUserPayload $payload): User
    {
        $user->update([
            'email' => $payload->email(),
        ]);

        return $user;
    }

    public function changePassword(User $user, ChangePasswordPayload $payload)
    {
        $user->update([
            'password' => Hash::make($payload->newPassword())
        ]);
    }
}
