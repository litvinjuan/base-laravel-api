<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Walsh\Models\User;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanChangePassword()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            route('auth.change'),
            [
                'oldPassword' => 'secret',
                'newPassword' => 'mynewpass',
                'newPassword_confirmation' => 'mynewpass',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertSuccessful();
    }

    public function testPasswordIsChanged()
    {
        $user = factory(User::class)->create();
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'secret',
        ]);
        $response->assertSuccessful();
        $response->assertJsonStructure(['token']);
        $token = $response['token'];

        $response = $this->postJson(
            route('auth.change'),
            [
                'oldPassword' => 'secret',
                'newPassword' => 'mynewpass',
                'newPassword_confirmation' => 'mynewpass',
            ],
            ['Authorization' => "Bearer $token"]
        );
        $response->assertSuccessful();

        // WithoutMiddleware because the request is not stateless and the guest middleware thinks we are logged in although we are not passing a token
        $response = $this->withoutMiddleware()->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'mynewpass',
        ]);
        $response->assertSuccessful();
        $response->assertJsonStructure(['token']);

        // WithoutMiddleware because the request is not stateless and the guest middleware thinks we are logged in although we are not passing a token
        $response = $this->withoutMiddleware()->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'secret',
        ]);
        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', trans('errors.auth.credentials'));
    }

    public function testOldPasswordIsRequired()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            route('auth.change'),
            [
                'newPassword' => 'mynewpass',
                'newPassword_confirmation' => 'mynewpass',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('errors.oldPassword.0', trans('validation.required', ['attribute' => 'old password']));
    }

    public function testOldPasswordMustBeCorrect()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            route('auth.change'),
            [
                'oldPassword' => 'wrongPass',
                'newPassword' => 'mynewpass',
                'newPassword_confirmation' => 'mynewpass',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('errors.oldPassword.0', trans('validation.custom.password.current'));
    }

    public function testNewPasswordIsRequired()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            route('auth.change'),
            [
                'oldPassword' => 'secret',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('errors.newPassword.0', trans('validation.required', ['attribute' => 'new password']));
    }

    public function testNewPasswordIsConfirmed()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            route('auth.change'),
            [
                'oldPassword' => 'secret',
                'newPassword' => 'mynewpass',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('errors.newPassword.0', trans('validation.confirmed', ['attribute' => 'new password']));
    }

    public function testNewPasswordMatchesConfirmation()
    {
        $user = factory(User::class)->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->postJson(
            route('auth.change'),
            [
                'oldPassword' => 'secret',
                'newPassword' => 'mynewpass',
                'newPassword_confirmation' => 'differentpass',
            ],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('errors.newPassword.0', trans('validation.confirmed', ['attribute' => 'new password']));
    }
}
