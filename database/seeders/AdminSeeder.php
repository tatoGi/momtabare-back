<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (! User::where('email', 'admin@admin.com')->exists()) {
            // If not, create the admin user
            User::create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'), // Update with your desired password
            ]);
        }

        // Add developer user
        if (! User::where('email', 'developer@dev.com')->exists()) {
            User::create([
                'name' => 'Developer',
                'email' => 'developer@dev.com',
                'password' => bcrypt('devpassword'), // Update with your desired password
            ]);
        }
    }
}
