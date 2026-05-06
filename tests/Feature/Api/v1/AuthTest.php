<?php

namespace Tests\Feature\Api\v1;

use App\Constants\SystemCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test CHW registration.
     */
    public function test_chw_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Jordy Parker',
            'phone' => '1234567890',
            'pin' => '123456',
            'region' => 'North',
            'health_program_id' => 'HP-001',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'code' => SystemCode::AUTH_REGISTER_SUCCESS,
                'message' => 'Registration successful',
            ]);

        $this->assertDatabaseHas('users', [
            'phone' => '1234567890',
            'name' => 'Jordy Parker',
        ]);
    }

    /**
     * Test CHW login.
     */
    public function test_chw_can_login(): void
    {
        $user = User::factory()->create([
            'phone' => '0987654321',
            'pin' => \Illuminate\Support\Facades\Hash::make('654321'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '0987654321',
            'pin' => '654321',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'code' => SystemCode::AUTH_LOGIN_SUCCESS,
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'access_token',
                    'token_type',
                ]
            ]);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'phone' => '1112223334',
            'pin' => \Illuminate\Support\Facades\Hash::make('111111'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'phone' => '1112223334',
            'pin' => '000000', // Still wrong because factory sets 123456
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => SystemCode::ERR_AUTH_INVALID_CREDENTIALS,
            ]);
    }

    /**
     * Test logout.
     */
    public function test_chw_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'code' => SystemCode::AUTH_LOGOUT_SUCCESS,
            ]);

        $this->assertEmpty($user->tokens);
    }
}
