<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login()
    {
        // Registro
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Matheus',
            'email' => 'matheus@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $registerResponse->assertStatus(200);
        $registerData = $registerResponse->json('data.authorisation');
        $this->assertArrayHasKey('token', $registerData);
        $this->assertNotEmpty($registerData['token']);

        // Login
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'matheus@example.com',
            'password' => 'secret123',
        ]);

        $loginResponse->assertStatus(200);
        $loginData = $loginResponse->json('data.authorisation');
        $this->assertArrayHasKey('token', $loginData);
        $this->assertNotEmpty($loginData['token']);
    }
}
