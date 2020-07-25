<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    const EMAIL = 'email';
    const PASSWORD = 'password';

    public function rules()
    {
        return [
            self::EMAIL => 'required|email',
            self::PASSWORD => 'required|string',
        ];
    }

    public function email(): string
    {
        return $this->input(self::EMAIL);
    }

    public function password(): string
    {
        return $this->input(self::PASSWORD);
    }

    public function credentials(): array
    {
        return $this->only(['email', 'password']);
    }

    public function remember(): bool
    {
        return $this->boolean('remember');
    }
}
