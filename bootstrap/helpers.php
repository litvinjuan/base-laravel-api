<?php

use Walsh\Models\User;
use Illuminate\Support\Facades\Auth;

if (! function_exists('current_user')) {
    function current_user(): ?User
    {
        /** @var User|null $user */
        static $user;

        if (! $user) {
            $user = Auth::user();
        }

        return $user;
    }
}
