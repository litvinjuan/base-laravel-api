<?php

namespace App\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CurrentPasswordRule implements Rule
{
    public function passes($attribute, $value)
    {
        return Hash::check($value, Auth::user()->getAuthPassword());
    }

    public function message()
    {
        return trans('validation.custom.password.current');
    }
}
