<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_creates_user_and_returns_message()
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'location' => 'Cotonou',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    /** @test */
    public function register_fails_when_email_already_taken()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_returns_access_token_and_user_when_credentials_are_valid_and_email_verified()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'access_token',
                     'token_type',
                     'user' => ['id', 'name', 'email']
                 ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Email ou mot de passe incorrect.']);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Déconnexion réussie.']);
    }

    /** @test */
    public function authenticated_user_can_get_their_info_via_me()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/auth/me');

        $response->assertStatus(200)
                 ->assertJson([
                     'user' => [
                         'id' => $user->id,
                         'email' => $user->email,
                     ]
                 ]);
    }

    /** @test */
    public function verify_email_with_valid_hash_marks_email_as_verified()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $hash = sha1($user->getEmailForVerification());

        $response = $this->postJson('/api/auth/verify-email', [
            'id' => $user->id,
            'hash' => $hash,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Email vérifié avec succès. Vous pouvez maintenant vous connecter.']);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** @test */
    public function resend_verification_email_sends_notification()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'email' => 'verify@example.com',
        ]);

        $response = $this->postJson('/api/auth/resend-verification-email', [
            'email' => 'verify@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Email de vérification renvoyé.']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
