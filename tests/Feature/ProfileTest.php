<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function verified_user_can_view_their_profile()
    {
        $user = User::factory()->verified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'email', 'role', 'reviews_count', 'comments_count'
            ]);
    }

    /** @test */
    public function unverified_user_cannot_view_profile()
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/profile');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_update_their_profile()
    {
        $user = User::factory()->verified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson('/api/profile', [
                'name' => 'New Name',
                'email' => 'newemail@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => 'New Name',
                    'email' => 'newemail@example.com',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);
    }

    /** @test */
    public function user_can_change_password()
    {
        $user = User::factory()->verified()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson('/api/profile/password', [
                'current_password' => 'oldpassword123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password changed successfully']);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /** @test */
    public function user_cannot_change_password_with_wrong_current_password()
    {
        $user = User::factory()->verified()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->putJson('/api/profile/password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Current password is incorrect']);
    }

    /** @test */
    public function user_can_delete_their_account()
    {
        $user = User::factory()->verified()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->deleteJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Account deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
