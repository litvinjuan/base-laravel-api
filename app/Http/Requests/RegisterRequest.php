<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Walsh\Payloads\User\CreateUserPayload;

class RegisterRequest extends FormRequest implements CreateUserPayload
{
    const EMAIL = 'email';
    const PASSWORD = 'password';

    public function rules()
    {
        return [
            self::EMAIL => [
                'required',
                'email',
                'unique:users,email',
            ],
            self::PASSWORD => [
                'required',
                'string',
            ],
        ];
    }

    public function email(): string
    {
        return $this->get(self::EMAIL);
    }

    public function password(): ?string
    {
        return $this->get(self::PASSWORD);
    }

    public function googleId(): ?string
    {
        return null;
    }
}
