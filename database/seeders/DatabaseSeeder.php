<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@combiphar.test'],
            ['name' => 'Admin', 'password' => bcrypt('password')],
        );

        $this->call(CmsSeeder::class);
    }
}