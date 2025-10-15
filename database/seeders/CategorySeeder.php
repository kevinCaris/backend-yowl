<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
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

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => fake()->sentence(15),
                ]
            );
        }
    }
}
