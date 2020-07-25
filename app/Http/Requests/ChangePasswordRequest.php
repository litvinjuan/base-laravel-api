<?php

namespace App\Http\Requests;

use App\Http\Rules\CurrentPasswordRule;
use Illuminate\Foundation\Http\FormRequest;
use Walsh\Payloads\User\ChangePasswordPayload;

class ChangePasswordRequest extends FormRequest implements ChangePasswordPayload
{
    const OLD_PASSWORD = 'oldPassword';
    const NEW_PASSWORD = 'newPassword';
    const NEW_PASSWORD_CONFIRMATION = 'newPassword_confirmation';

    public function rules()
    {
        return [
            self::OLD_PASSWORD => [
                'required',
                'string',
                new CurrentPasswordRule,
            ],
            self::NEW_PASSWORD => [
                'required',
                'string',
                'confirmed'
            ],
        ];
    }

    public function oldPassword(): string
    {
        return $this->get(self::OLD_PASSWORD);
    }

    public function newPassword(): string
    {
        return $this->get(self::NEW_PASSWORD);
    }
}
