<?php

namespace Walsh\Payloads\User;

interface CreateUserPayload
{
    public function email(): string;

    public function password(): ?string;

    public function googleId(): ?string;
}
