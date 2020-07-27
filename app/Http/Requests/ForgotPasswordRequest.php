<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    const EMAIL = 'email';

    public function rules()
    {
        return [
            self::EMAIL => [
                'required',
                'email',
            ],
        ];
    }

    private function email(): string
    {
        return $this->input(self::EMAIL);
    }

    public function credentials()
    {
        return [
            'email' => $this->email()
        ];
    }
}
