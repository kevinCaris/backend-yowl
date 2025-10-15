<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Créer un admin de test
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@greelogix.com',
            'avatar'=>'https://cdn-icons-png.flaticon.com/512/3033/3033143.png',
            'password' => bcrypt('Admin@123'),
        ]);

        // Créer un utilisateur normal de test
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@greelogix.com',
            'avatar'=>'https://cdn-icons-png.flaticon.com/512/3033/3033143.png',
            'password' => bcrypt('Test@123'),
        ]);


        // Create 20 normal users
        User::factory(20)->create();
    }
}
