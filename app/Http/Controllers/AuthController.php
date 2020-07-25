<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Util\SocialiteCreateUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Walsh\Exceptions\AuthException;
use Walsh\Models\User;
use Walsh\Services\UserService;

class AuthController
{
    public function current()
    {
        return current_user();
    }

    public function login(LoginRequest $request)
    {
        /** @var Builder $query */
        $query = User::query()->where('email', $request->email());

        if ($query->doesntExist()) {
            throw ValidationException::withMessages(['password' => trans('errors.auth.credentials')]);
        }

        if ($query->fromSocialite(false)->doesntExist()) {
            throw ValidationException::withMessages(['email' => trans('errors.auth.user-from-socialite')]);
        }

        /** @var User $user */
        $user = $query->first();
        if (! Hash::check($request->password(), $user->getAuthPassword())) {
            throw ValidationException::withMessages(['password' => trans('errors.auth.credentials')]);
        }

        $token = $user->createToken('browser')->plainTextToken;

        return [
            'token' => $token,
        ];
    }

    public function register(RegisterRequest $request, UserService $userService)
    {
        $user = $userService->create($request);

        $token = $user->createToken('browser')->plainTextToken;

        return [
            'token' => $token,
        ];
    }

    public function logout()
    {
        current_user()->currentAccessToken()->delete();
    }

    public function redirect()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return response()->json(['redirectUrl' => $url], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function callback(UserService $userService)
    {
        try {
            $socialiteUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            throw AuthException::callbackError($e->getMessage());
        }

        $user = User::query()->where('email', $socialiteUser->email)->first();
        if (! $user) {
            $user = $userService->create(new SocialiteCreateUser($socialiteUser));
        }

        if ($user->google_id != $socialiteUser->getId()) {
            throw ValidationException::withMessages(['generic' => trans('errors.auth.socialite-different-google-id')]);
        }

        $token = $user->createToken('browser')->plainTextToken;

        return [
            'token' => $token
        ];
    }
}
