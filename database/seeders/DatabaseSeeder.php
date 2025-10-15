<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Créer un admin de test
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@greelogix.com',
            'password' => 'Admin@123',
        ]);

        // Créer un utilisateur normal de test
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@greelogix.com',
            'password' => 'Test@123',
        ]);

        // Créer 10 utilisateurs aléatoires
        $users = User::factory(10)->create();

        // Ajouter l'admin et le test user à la collection
        $allUsers = $users->push($admin)->push($testUser);

        // Créer 10 catégories
        //$categories = Category::factory(10)->create();

        // Créer  reviews avec des utilisateurs et catégories aléatoires
        Review::factory(30)->create()->each(function ($review) use ($allUsers) {
            $review->user_id = $allUsers->random()->id;
            //$review->category_id = $categories->random()->id;
            $review->save();

            // Créer 3 à 8 commentaires par review
            $commentsCount = rand(3, 8);
            for ($i = 0; $i < $commentsCount; $i++) {
                $comment = Comment::factory()->create([
                    'user_id' => $allUsers->random()->id,
                    'review_id' => $review->id,
                ]);

                // 50% de chance d'avoir 1 à 3 réponses
                if (rand(0, 1)) {
                    $repliesCount = rand(1, 3);
                    for ($j = 0; $j < $repliesCount; $j++) {
                        Comment::factory()->reply($comment->id)->create([
                            'user_id' => $allUsers->random()->id,
                            'review_id' => $review->id,
                        ]);
                    }
                }
            }
        });

        $this->command->info(' Base de données peuplée avec succès !');
        $this->command->info('Admin : admin@greelogix.com / Admin@123');
        $this->command->info('User : test@greelogix.com / Test@123');
        // $this->call([
        //     UserSeeder::class,
        // ]);
    }
}
