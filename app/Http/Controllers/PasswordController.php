<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Walsh\Models\User;
use Walsh\Services\UserService;

class PasswordController
{
    public function change(ChangePasswordRequest $request, UserService $userService)
    {
        $userService->changePassword(current_user(), $request);
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        $response = Password::sendResetLink($request->credentials());

        if ($response == Password::INVALID_USER) {
            return response()->noContent(204); // Don't disclose the fact that the email is invalid
        }

        if ($response != Password::RESET_LINK_SENT) {
            throw PasswordException::couldNotSendResetEmail($response);
        }

        return response()->noContent(204);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $response = Password::reset($request->credentials(), function (User $user, $password) {
            $user->update([
                'remember_token' => Str::random(60),
                'password' => Hash::make($password),
            ]);

            event(new PasswordReset($user));
        });

        if ($response != Password::PASSWORD_RESET) {
            throw PasswordException::resetError();
        }

        /** @var User $user */
        $user = User::query()
            ->where('email', $request->email())
            ->firstOrFail();
        $token = $user->createToken('browser')->plainTextToken;

        return [
            'token' => $token,
        ];
    }
}
