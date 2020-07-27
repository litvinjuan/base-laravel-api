<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Walsh\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanLogin()
    {
        $user = factory(User::class)->create();
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['token']);
    }

    public function testGuestCannotAccessAuthenticatedRoute()
    {
        $response = $this->getJson(route('auth.current'));

        $response->assertStatus(401);
        $response->assertJsonPath('message', 'Unauthenticated.');
    }

    public function testLoginTokenServesAsAuthentication()
    {
        $user = factory(User::class)->create();
        $loginResponse = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'secret']);
        $token = $loginResponse['token'];

        $response = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token"]);

        $response->assertSuccessful();
        $response->assertJsonPath('id', $user->id);
        $response->assertJsonPath('email', $user->email);
    }

    public function testInvalidTokenIsUseless()
    {
        $user = factory(User::class)->create();
        $loginResponse = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'secret']);
        $token = $loginResponse['token'];

        $response = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token-fake-token-part"]);

        $response->assertStatus(401);
    }

    public function testUserWithWrongPasswordCannotLogin()
    {
        $user = factory(User::class)->create();
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'mywrongsecretpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', trans('errors.auth.credentials'));
    }

    public function testNoUserCannotLogin()
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => 'fakeemail@gmail.com',
            'password' => 'mywrongsecretpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', trans('errors.auth.credentials'));
    }

    public function testCannotLoginWithoutPassword()
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => 'fakeemail@gmail.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.password.0', trans('validation.required', ['attribute' => 'password']));
    }

    public function testCannotLoginWithoutEmail()
    {
        $response = $this->postJson(route('auth.login'), [
            'password' => 'mywrongsecretpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', trans('validation.required', ['attribute' => 'email']));
    }

    public function testCannotLoginWithInvalidEmail()
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => 'thisisnotanemailaddress',
            'password' => 'mywrongsecretpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', trans('validation.email', ['attribute' => 'email']));
    }

    public function testCannotAccessGuestRouteWhenAuthenticated()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $user->createToken('browser')->plainTextToken;

        $response = $this->postJson(
            route('auth.login'),
            ['email' => $user->email, 'password' => 'secret'],
            ['Authorization' => "Bearer $token"]
        );

        $response->assertStatus(401);
        $response->assertJsonPath('message', trans('errors.auth.already-authenticated'));
    }
}
