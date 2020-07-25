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

        $logoutResponse = $this->postJson(route('auth.logout'), ['Authorization' => "Bearer $token"]);
        $logoutResponse->assertSuccessful();

//        $unauthenticatedResponse = $this->getJson(route('auth.current'), ['Authorization' => "Bearer $token"]);
//        $unauthenticatedResponse->assertStatus(401);
    }
}
