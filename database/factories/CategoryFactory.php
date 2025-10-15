<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected static $categories = [
        'Électronique',
        'Mode & Vêtements',
        'Maison & Jardin',
        'Sports & Loisirs',
        'Beauté & Santé',
        'Alimentation',
        'Livres & Médias',
        'Jouets & Enfants',
        'Automobile',
        'High-Tech',
    ];
    public function definition(): array
    {
        // On récupère un nom de catégorie, ou on en génère un nouveau si la liste est vide
        $name = array_shift(static::$categories) ?? $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(15),
        ];
    }
}
