<?php

namespace App\Http\Util;

use Laravel\Socialite\Contracts\User;
use Walsh\Payloads\User\CreateUserPayload;

class SocialiteCreateUser implements CreateUserPayload
{
    /** @var string */
    private $googleId;
    /** @var string */
    private $email;

    public function __construct(User $user)
    {
        $this->email = $user->getEmail();
        $this->googleId = $user->getId();
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): ?string
    {
        return null;
    }

    public function googleId(): ?string
    {
        return $this->googleId;
    }
}
