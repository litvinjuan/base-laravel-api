<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    const TOKEN = 'token';
    const EMAIL = 'email';
    const PASSWORD = 'password';
    const PASSWORD_CONFIRMATION = 'password_confirmation';

    public function rules()
    {
        return [
            self::TOKEN => [
                'required',
                'string',
            ],
            self::EMAIL => [
                'required',
                'email',
            ],
            self::PASSWORD => [
                'required',
                'string',
                'confirmed',
            ],
        ];
    }

    public function credentials(): array
    {
        return $this->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    public function email(): string
    {
        return $this->input(self::EMAIL);
    }
}
