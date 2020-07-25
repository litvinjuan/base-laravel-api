<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Walsh\Models\User;

class SocialiteTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestCanRequestRedirect()
    {
        $response = $this->getJson(route('auth.redirect'));

        $response->assertSuccessful();
        $response->assertJsonStructure(['redirectUrl']);
    }

    public function testUserCannotRequestRedirect()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $token = $user->createToken('browser')->plainTextToken;
        $response = $this->getJson(route('auth.redirect'), ['Authorization' => "Bearer $token"]);

        $response->assertStatus(401);
        $response->assertJsonPath('message', trans('errors.auth.already-authenticated'));
    }

    public function testRedirectUrlIsValid()
    {
        $validUrlRegex = "/^(https:\/\/accounts\.google\.com\/o\/oauth2\/auth\?client_id=[\w\-\.]+&redirect_uri=[\w\-\.%]+&scope=[\w+]+&response_type=[\w]+)$/";
        $response = $this->getJson(route('auth.redirect'));

        $response->assertSuccessful();
        $response->assertJsonStructure(['redirectUrl']);

        $redirectUrl = $response['redirectUrl'];
        $this->assertRegExp($validUrlRegex, $redirectUrl);
    }

    public function testSocialiteUserCannotLoginWithPassword()
    {
        /** @var User $user */
        $user = factory(User::class)->state('socialite')->create();
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('errors.email.0', trans('errors.auth.user-from-socialite'));
    }

//    public function testNonSocialiteUserCanLoginWithSocialite()
//    {
//        // Mock Socialite, create non-socialite user, mock socialite user email as the user's email, login should be successful and user should have a google_id
//        $response = $this->postJson(route('auth.login'), [
//            'email' => 'fakeemail@gmail.com',
//        ]);
//
//        $response->assertStatus(422);
//        $response->assertJsonPath('errors.password.0', trans('validation.required', ['attribute' => 'password']));
//    }
//
//    public function testSocialiteTokenIsValid()
//    {
//        // Test the token retured after the socialite callback is valid and works for auth
//        $user = factory(User::class)->create();
//        $loginResponse = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'secret']);
//        $token = $loginResponse['token'];
//
//        $response = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token"]);
//
//        $response->assertSuccessful();
//        $response->assertJsonPath('id', $user->id);
//        $response->assertJsonPath('email', $user->email);
//    }
//
//    public function testGuestCanLogin()
//    {
//        // Test a guest can login using socialite
//        $response = $this->postJson(route('auth.login'), [
//            'email' => 'fakeemail@gmail.com',
//            'password' => 'mywrongsecretpassword',
//        ]);
//
//        $response->assertStatus(422);
//        $response->assertJsonPath('errors.password.0', trans('errors.auth.credentials'));
//    }
//
//    public function testGuestCanCreateAccount()
//    {
//        // Test a guest can create an account using socialite
//        $response = $this->postJson(route('auth.login'), [
//            'email' => 'fakeemail@gmail.com',
//        ]);
//
//        $response->assertStatus(422);
//        $response->assertJsonPath('errors.password.0', trans('validation.required', ['attribute' => 'password']));
//    }
}
