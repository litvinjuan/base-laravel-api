<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CurrentPasswordRule implements Rule
{
    public function passes($attribute, $value)
    {
        return Hash::check(Auth::user()->getAuthPassword(), $value);
    }

    public function message()
    {
        return 'La contraseña que ingresaste es incorrecta';
    }
}
