<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            //'category_id' => Category::factory(),
            'product_name' => $this->faker->words(3, true),
            'product_link' => $this->faker->url(),
            'product_image' => $this->faker->imageUrl(640, 480, 'products', true),
            'tag' => $this->faker->words(3, true),
            'content' => $this->faker->paragraphs(3, true),
            'rating' => $this->faker->numberBetween(1, 5),
            'likes_count' => $this->faker->numberBetween(0, 100),
            'dislikes_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}
