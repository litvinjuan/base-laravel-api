<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Walsh\Models\User;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCannotRegister()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $user->createToken('browser')->plainTextToken;

        $response = $this->postJson(
            route('auth.register'),
            ['email' => 'test@example.com', 'password' => 'secret'],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(401);
        $response->assertJsonPath('message', trans('errors.auth.already-authenticated'));
    }

    public function testGuestCanRegister()
    {
        $this->assertCount(0, User::all());
        $response = $this->postJson(route('auth.register'), [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['token']);
        $this->assertCount(1, User::all());

        $user = User::query()->find(1);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function testRegisteredAccountIsNonSocialite()
    {
        $registrationResponse = $this->postJson(route('auth.register'), [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);

        /** @var User $user */
        $user = User::query()->find(1);

        $this->assertFalse($user->isFromSocialite());
    }

    public function testRegistrationReturnsValidToken()
    {
        $registrationResponse = $this->postJson(route('auth.register'), [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);
        $token = $registrationResponse['token'];

        $response = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token"]);

        $user = User::query()->find(1);

        $response->assertSuccessful();
        $response->assertJsonPath('id', $user->id);
    }

    public function testCannotRegisterDuplicateEmail()
    {
        $user = factory(User::class)->create();
        $response = $this->postJson(route('auth.register'), [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', trans('validation.unique', ['attribute' => 'email']));
    }

    public function testCannotRegisterWithoutPassword()
    {
        $response = $this->postJson(route('auth.register'), [
            'email' => 'fakeemail@gmail.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', trans('validation.required', ['attribute' => 'password']));
    }

    public function testCannotRegisterWithoutEmail()
    {
        $response = $this->postJson(route('auth.register'), [
            'password' => 'mywrongsecretpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', trans('validation.required', ['attribute' => 'email']));
    }

    public function testCannotRegisterWithInvalidEmail()
    {
        $response = $this->postJson(route('auth.register'), [
            'email' => 'thisisnotanemailaddress',
            'password' => 'mywrongsecretpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', trans('validation.email', ['attribute' => 'email']));
    }
}
