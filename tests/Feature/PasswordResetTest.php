<?php

namespace Tests\Feature;

use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Walsh\Models\User;
use Walsh\Notifications\ResetPassword;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function testEmailGetsSent()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Notification::fake();
        Notification::assertNothingSent();

        $this->postJson(route('auth.forgot'), ['email' => $user->email])
            ->assertSuccessful()
            ->assertNoContent();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function testEmailHasCorrectParams()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        Notification::fake();

        $this->postJson(route('auth.forgot'), ['email' => $user->email])
            ->assertSuccessful()
            ->assertNoContent();

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification, $channels) use ($user) {
            $token = $notification->token;
            $email = urlencode($user->email);

            $expectedUrl = config('auth.reset-notification-url');
            $expectedUrl .= "?token=$token&email=$email";

            return $notification->toMail($user)->actionUrl === $expectedUrl;
        });
    }

    public function testSuccessDespiteInvalidEmail()
    {
        $this->postJson(route('auth.forgot'), ['email' => 'fake@email.com'])
            ->assertSuccessful()
            ->assertNoContent();
    }

    public function testEmailMustBePresent()
    {
        $this->postJson(route('auth.forgot'))
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', trans('validation.required', ['attribute' => 'email']));
    }

    public function testEmailMustBeValid()
    {
        $this->postJson(route('auth.forgot'), ['email' => 'fakeemail.com'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', trans('validation.email', ['attribute' => 'email']));
    }

    public function testEmailNotSentWithInvalidEmail()
    {
        Notification::fake();
        Notification::assertNothingSent();

        $this->postJson(route('auth.forgot'), ['email' => 'fake@email.com']);

        Notification::assertNothingSent();
    }

    public function testPasswordCanBeReset()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $this->createToken($user);

        $response = $this->postJson(
            route('auth.reset', ['email' => $user->email, 'token' => $token]),
            ['password' => 'newpass', 'password_confirmation' => 'newpass']
        );

        $response->assertSuccessful();
        $response->assertJsonStructure(['token']);
        $this->assertTrue(Hash::check('newpass', $user->refresh()->getAuthPassword()));
    }

    public function testResetPasswordCanBeUsedToLogin()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $this->createToken($user);

        $this->postJson(
            route('auth.reset', ['email' => $user->email, 'token' => $token]),
            ['password' => 'newpass', 'password_confirmation' => 'newpass']
        );

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'newpass',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['token']);
    }

    public function testPasswordIsRequired()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $this->createToken($user);

        $response = $this->postJson(
            route('auth.reset', ['email' => $user->email, 'token' => $token]),
            []
        );

        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', trans('validation.required', ['attribute' => 'password']));
    }

    public function testPasswordMustBeConfirmed()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $this->createToken($user);

        $response = $this->postJson(
            route('auth.reset', ['email' => $user->email, 'token' => $token]),
            ['password' => 'newpass']
        );

        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', trans('validation.confirmed', ['attribute' => 'password']));
    }

    private function createToken(User $user)
    {
        /** @var PasswordBroker $broker */
        $broker = app(PasswordBroker::class);

        return $broker->createToken($user);
    }
}
