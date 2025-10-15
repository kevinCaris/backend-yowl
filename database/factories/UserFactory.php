<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'avatar' => 'https://cdn-icons-png.flaticon.com/512/3033/3033143.png',
            'birth_date' => fake()->dateTimeBetween('-40 years', '-13 years')->format('Y-m-d'),
            'location' => fake()->city() . ', ' . fake()->country(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'user',
            'is_reliable' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Créer un administrateur
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Créer un utilisateur non fiable (banni)
     */
    public function unreliable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_reliable' => false,
        ]);
    }

    /**
     * Créer un utilisateur avec email non vérifié
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
