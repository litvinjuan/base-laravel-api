<?php

namespace Walsh\Payloads\User;

interface ChangePasswordPayload
{
    public function oldPassword(): string;

    public function newPassword(): string;
}
