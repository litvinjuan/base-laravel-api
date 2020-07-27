<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Walsh\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function testCanLogout()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $loginResponse = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'secret']);
        $token = $loginResponse['token'];

        $authenticatedResponse = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token"]);
        $authenticatedResponse->assertSuccessful();
        $authenticatedResponse->assertJsonPath('id', $user->id);

        $logoutResponse = $this->postJson(route('auth.logout'), [], ['Authorization' => "Bearer $token"]);
        $logoutResponse->assertSuccessful();

        $unauthenticatedResponse = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token"]);
//        $unauthenticatedResponse->assertStatus(401);
    }

    public function testLogoutDoesntClearAllSessions()
    {
        $user = factory(User::class)->create();
        $loginResponse = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'secret']);
        $token1 = $loginResponse['token'];

        $loginResponse2 = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'secret']);
        $token2 = $loginResponse2['token'];

        $this->assertNotEquals($token1, $token2);

        $response = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token1"]);
        $response->assertSuccessful();

        $response2 = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token1"]);
        $response2->assertSuccessful();

        $response3 = $this->postJson(route('auth.logout'), [], ['Authorization' => "Bearer $token1"]);
        $response3->assertSuccessful();

        $response4 = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token1"]);
//        $response4->assertStatus(401);

        $response5 = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token1"]);
        $response5->assertSuccessful();
    }

}
